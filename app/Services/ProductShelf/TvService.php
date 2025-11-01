<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Product\ProductAttribute;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Models\Shelf\ShelvesTemp;
use App\Services\Shelf\ShelfTempService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TvService implements ProductShelfInterface
{
    public float $default = 70.9;
    public float $space = 15;
    public float $paddon_size = 270;

    public function createTemp(Shelf $shelf): void
    {
        $ordering = 1;

        if ($shelf->is_paddon) {
            for ($i = 1; $i < $shelf->paddon_quantity + 1; $i++) {
                $ordering = $this->dialProduct($shelf->id, $i, 'paddon', $this->paddon_size, $ordering, 1);
            }

            for ($i = 1; $i < $shelf->paddon_quantity + 1; $i++) {
                $ordering = $this->dialProduct($shelf->id, $i, 'paddon_back', $this->paddon_size, $ordering, 1);
            }
        }

        for ($i = 1; $shelf->floor + 1 > $i; $i++) {
            $ordering = $this->dialProduct($shelf->id, $i, 'center', $shelf->size, $ordering, 1);
        }

        for ($i = 1; $shelf->floor_left + 1 > $i; $i++) {
            if (!is_null($shelf->left_size)) {
                $ordering = $this->dialProduct($shelf->id, $i, 'left', $shelf->left_size, $ordering, 1);
            }
        }

        for ($i = 1; $shelf->floor_right + 1 > $i; $i++) {
            if (!is_null($shelf->right_size)) {
                $ordering = $this->dialProduct($shelf->id, $i, 'right', $shelf->right_size, $ordering, 1);
            }
        }
    }

    public function dialProduct($shelf_id, $floor, $place, $length, $ordering, $floor_ordering)
    {
        $i = 0;
        $default = $this->default;
        $space = $floor_ordering == 1 ? 0 : $this->space;

        while ($length > $default + $space) {
            $i++;
            $space = $floor_ordering == 1 ? 0 : $this->space;
            $length = $length - ($default + $space);
            if ($length < 0) break;

            ShelfTempService::tempAddEmptyProduct($shelf_id, $ordering, $place, $floor, $i, $default);
            $ordering++;
            $floor_ordering++;
        }

        (new ShelfTempService())->addShelfTemp($shelf_id, $place, $floor, $length);
        return $ordering;
    }

    public function tempAddProduct(array $data): void
    {
        $attr = ProductAttribute::query()->where('sku', $data['sku'])->first();
        $temp = ProductShelfTemp::query()->where('id',$data['temp_id'])->first();

        if ($data['shelf']->category_sku != $attr->category_sku) throwError(trans("errors.shelf.category_different"));
        BaseTempService::checkDublProduct($data);

        DB::beginTransaction();

        try {
            if (is_null($temp->sku)) {
                if (is_null($attr->size)) throwError(__('product.weight_not_found'));

                if ($attr->size == $temp->size){
                    $temp->sku = $data['sku'];
                    $temp->is_sold = false;
                    $temp->sold_at = null;
                    $temp->save();
                } else {
                    $order['ordering'] = $data['ordering'];
                    $order['sku'] = $data['sku'];
                    $order['size'] = $attr->size;
                    $order['place'] = $temp->place;
                    $order['floor'] = $temp->floor;
                    $order['floor_ordering'] = $temp->floor_ordering;
                    $this->updateProductTempV2($data['shelf'], $order);
                }

                DB::commit();
            } else {
                throwError(__("errors.shelf.product_exists_in_this_ordering"));
            }
        } catch (\Throwable $e) {
            throwResponse($e);
        }
    }

    public function updateProductTempV2($shelf, $order)
    {
        $before_temp = ProductShelfTemp::query()
            ->where('shelf_id', $shelf->id)
            ->where('place', $order['place'])
            ->where('floor', $order['floor'])
            ->whereNull('sku')
            ->where('ordering', '<', $order['ordering'])
            ->orderByDesc('ordering')
            ->get();

        $after_temp = ProductShelfTemp::query()
            ->where('shelf_id', $shelf->id)
            ->where('place', $order['place'])
            ->where('floor', $order['floor'])
            ->whereNull('sku')
            ->where('ordering', '>=', $order['ordering'])
            ->orderBy('ordering')
            ->get();

        $is_fit = false;
        $fit_len = 0;
        $after_temp_array = (clone $after_temp)->toArray();
        $space = $order['floor_ordering'] == 1 ? 0 : 15;
        $del_products = [];

        $shelves_temp = ShelvesTemp::query()
            ->where('shelf_id', $shelf->id)
            ->where('place', $order['place'])
            ->where('floor', $order['floor'])
            ->first();

        $shelves_temp_length = $shelves_temp->excess ?? 0;
        $fit_len += $shelves_temp_length;

        foreach ($after_temp_array as $index => $temp) {
            $fit_len += $temp['size'] + ($order['floor_ordering'] == 1 && $index == 0 ? 0 : 15);

            if ($index !== 0) {
                $del_products[] = $temp;
            }

            if ($fit_len > $order['size'] + $space) {
                $is_fit = true;
                break;
            }
        }

        if ($is_fit) {
            ProductShelfTemp::query()
                ->where('shelf_id', $shelf->id)
                ->where('place', $order['place'])
                ->where('floor', $order['floor'])
                ->where('ordering', $order['ordering'])
                ->update([
                    'sku'     => $order['sku'],
                    'size'    => $order['size'],
                    'is_sold' => false,
                    'sold_at' => null,
                ]);

            foreach ($del_products as $del_product) {
                ProductShelfTemp::query()->find($del_product['id'])->delete();
            }

            $after_products = ProductShelfTemp::query()
                ->where('shelf_id', $shelf->id)
                ->where('place', $order['place'])
                ->where('floor', $order['floor'])
                ->where('floor_ordering', '>', $order['floor_ordering'])
                ->orderBy('floor_ordering')
                ->get();

            if (count($after_products) > 0) {
                $ordering = $order['floor_ordering'];
                foreach ($after_products as $after_product) {
                    $ordering++;
                    $after_product->floor_ordering = $ordering;
                    $after_product->save();
                }
            }

            $after_products_2 = ProductShelfTemp::query()
                ->where('shelf_id', $shelf->id)
                ->where('ordering', '>', $order['ordering'])
                ->orderBy('ordering')
                ->get();

            if (count($after_products_2) > 0) {
                $ordering = $order['ordering'];
                foreach ($after_products_2 as $after_product_2) {
                    $ordering++;
                    $after_product_2->ordering = $ordering;
                    $after_product_2->save();
                }
            }

            ShelvesTemp::query()
                ->where('shelf_id', $shelf->id)
                ->where('place', $order['place'])
                ->where('floor', $order['floor'])
                ->update(['excess' => $fit_len - ($order['size'] + $space)]);
        } else {
            foreach ($before_temp as $index => $temp) {
                $fit_len += $temp['size'] + ($order['floor_ordering'] == 1 && $index == 0 ? 0 : 15);

                $del_products[] = $temp->toArray();

                if ($fit_len > $order['size'] + $space) {
                    $is_fit = true;
                    break;
                }
            }

            if ($is_fit) {
                foreach ($del_products as $del_product) {
                    ProductShelfTemp::query()->find($del_product['id'])->delete();
                }

                $before_temp = ProductShelfTemp::query()
                    ->where('shelf_id', $shelf->id)
                    ->where('place', $order['place'])
                    ->where('floor', $order['floor'])
                    ->whereNull('sku')
                    ->where('ordering', '<', $order['ordering'])
                    ->orderByDesc('ordering')
                    ->first();

                ProductShelfTemp::query()
                    ->where('shelf_id', $shelf->id)
                    ->where('place', $order['place'])
                    ->where('floor', $order['floor'])
                    ->where('ordering', $order['ordering'])
                    ->update([
                        'sku'            => $order['sku'],
                        'size'           => $order['size'],
                        'is_sold'        => false,
                        'sold_at'        => null,
                        'ordering'       => $before_temp['ordering'] + 1,
                        'floor_ordering' => $before_temp['floor_ordering'] + 1,
                    ]);

                ShelvesTemp::query()
                    ->where('shelf_id', $shelf->id)
                    ->where('place', $order['place'])
                    ->where('floor', $order['floor'])
                    ->update(['excess' => $fit_len - ($order['size'] + $space)]);
            } else {
                throwError(trans("errors.shelf.not_enough_space"));
            }
        }
    }

    public function deleteTempProduct(ProductShelfTemp $temp): void
    {
        DB::beginTransaction();

        try {
            if ($temp->size == $this->default) {
                $temp->sku = null;
                $temp->is_sold = false;
                $temp->sold_at = null;
                $temp->size = $this->default;
                $temp->save();
            } else {
                $shelves_temp = ShelvesTemp::query()
                    ->where('shelf_id', $temp->shelf_id)
                    ->where('place', $temp->place)
                    ->where('floor', $temp->floor)
                    ->first();

                $shelves_temp_length = $shelves_temp->excess ?? 0;
                $space = $temp->floor_ordering == 1 ? 0 : 15;
                $length = $temp->size + $shelves_temp_length + $space;

                $count = 0;
                while ($length > $this->default + ($temp->floor_ordering == 1 && $count == 0 ? 0 : 15)) {
                    $space = ($temp->floor_ordering == 1 && $count == 0 ? 0 : 15);
                    $length -= $this->default + $space;
                    if ($length > $this->default + ($temp->floor_ordering == 1 && $count == 0 ? 0 : 15)) $count++;
                    if ($length < $this->default + $space) break;
                }

                $temp->sku = null;
                $temp->is_sold = false;
                $temp->sold_at = null;
                $temp->size = $this->default;
                $temp->save();

                $shelves_temp->update(['excess' => $length]);

                $ordering = $temp->ordering;
                $floor_ordering = $temp->floor_ordering;
                $created_tv = [];
                for ($i = 0; $i < $count; $i++) {
                    $ordering++;
                    $floor_ordering++;
                    $new = ShelfTempService::tempAddEmptyProduct($temp->shelf_id, $ordering, $temp->place, $temp->floor, $floor_ordering, $this->default);
                    $created_tv[] = $new->toArray()['id'];
                }

                $after_temp = ProductShelfTemp::query()
                    ->where('shelf_id', $temp->shelf_id)
                    ->where('place', $temp->place)
                    ->where('floor', $temp->floor)
                    ->where('floor_ordering', '>', $temp->floor_ordering)
                    ->whereNotIn('id', $created_tv)
                    ->orderBy('floor_ordering')
                    ->get();

                $floor_ordering = $temp->floor_ordering + $count;
                foreach ($after_temp as $temp_2) {
                    $floor_ordering++;
                    $temp_2->floor_ordering = $floor_ordering;
                    $temp_2->save();
                }

                $after_temp_2 = ProductShelfTemp::query()
                    ->where('shelf_id', $temp->shelf_id)
                    ->where('ordering', '>', $temp->ordering)
                    ->whereNotIn('id', $created_tv)
                    ->orderBy('ordering')
                    ->get();

                $ordering = $temp->ordering + $count;
                foreach ($after_temp_2 as $item) {
                    $ordering++;
                    $item->ordering = $ordering;
                    $item->save();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            throwResponse($e);
        }
    }

    public function tempAutoOrderProduct(Shelf $shelf, array $priority): Collection
    {
        DB::beginTransaction();

        if (is_null($shelf->size)) throwError('No shelf size');

        $stock = $this->getStock($shelf, []);

        if (isset($priority[0]['price'])) {
            $stock->orderBy('products.price', $priority[0]['price']);
        }

        $ordering_count = 0;
        $floor_count = 1;
        $floor_ordering = 0;
        $left_skus = [];
        $center_skus = [];
        $right_skus = [];
        $paddon_skus = [];

        /* =========== LEFT ORDERING =========== */
        if (!is_null($shelf->left_size)) {
            $shelf_size = $shelf->left_size;
            foreach ($stock->get() as $product) {
                if (is_null($product->attribute->size)) continue;
                $product_size = $floor_ordering === 1 ? $product->attribute->size : $product->attribute->size + 15;

                if ($shelf_size < $product_size) {
                    // Keyingi qavatga product terish uchun yangilanish
                    if (!is_null($shelf->floor) && $floor_count < $shelf->floor) {
                        $floor_count++;
                        $floor_ordering = 0;
                        $shelf_size = $shelf->left_size;
                    }
                }

                if (($floor_count === 3 && $product->attribute->dioganal > 43.00) ||
                    ($floor_count === 2 && $product->attribute->dioganal > 50.00)) {
                    continue;
                }

                // Agar product sig'sa shelfga terish
                if ($product_size <= $shelf_size) {
                    $floor_ordering++;
                    $shelf_size -= $product_size;
                    $ordering_count++;

                    $shelfTemp = ProductShelfTemp::query()
                        ->where('shelf_id', $shelf->id)
                        ->where('ordering', $ordering_count);

                    $shelfTempData = [
                        'sku' => $product->sku,
                        'size' => $product->attribute->size,
                        'place' => 'left',
                        'floor' => $floor_count,
                        'floor_ordering' => $floor_ordering,
                        'is_sold' => false,
                        'sold_at' => null,
                    ];

                    if ($shelfTemp->exists()) {
                        $shelfTemp->update($shelfTempData);
                    } else {
                        ProductShelfTemp::query()->create(array_merge(['shelf_id' => $shelf->id, 'ordering' => $ordering_count], $shelfTempData));
                    }
                    $left_skus[] = $product->sku;
                }
            }
        }

        /* =========== CENTER ORDERING =========== */
        $floor_count = 1;
        $floor_ordering = 0;
        $shelf_size = $shelf->size;
        if (!is_null($shelf->size)) {
            $stock = $this->getStock($shelf, $left_skus);

            foreach ($stock->get() as $product) {
                if (is_null($product->attribute->size)) continue;
                $product_size = $floor_ordering === 1 ? $product->attribute->size : $product->attribute->size + 15;

                if ($shelf_size < $product_size) {
                    // Keyingi qavatga product terish uchun yangilanish
                    if (!is_null($shelf->floor) && $floor_count < $shelf->floor) {
                        $floor_count++;
                        $floor_ordering = 0;
                        $shelf_size = $shelf->size;
                    }
                }

                if (($floor_count === 3 && $product->attribute->dioganal > 43.00) ||
                    ($floor_count === 2 && $product->attribute->dioganal > 50.00)) {
                    continue;
                }

                // Agar product sig'sa shelfga terish
                if ($product_size <= $shelf_size) {
                    $floor_ordering++;
                    $shelf_size -= $product_size;

                    $ordering_count++;
                    $shelfTemp = ProductShelfTemp::query()
                        ->where('shelf_id', $shelf->id)
                        ->where('ordering', $ordering_count);

                    $shelfTempData = [
                        'sku' => $product->sku,
                        'size' => $product->attribute->size,
                        'place' => 'center',
                        'floor' => $floor_count,
                        'floor_ordering' => $floor_ordering,
                        'is_sold' => false,
                        'sold_at' => null,
                    ];

                    if ($shelfTemp->exists()) {
                        $shelfTemp->update($shelfTempData);
                    } else {
                        ProductShelfTemp::query()->create(array_merge(['shelf_id' => $shelf->id, 'ordering' => $ordering_count], $shelfTempData));
                    }
                    $center_skus[] = $product->sku;
                }
            }
        }

        /* =========== RIGHT ORDERING =========== */
        $floor_count = 1;
        $floor_ordering = 0;
        $shelf_size = $shelf->right_size;
        if (!is_null($shelf->right_size)) {
            $stock = $this->getStock($shelf, array_merge($left_skus, $center_skus));

            foreach ($stock->get() as $product) {
                if (is_null($product->attribute->size)) continue;
                $product_size = $floor_ordering === 1 ? $product->attribute->size : $product->attribute->size + 15;

                if ($shelf_size < $product_size) {
                    // Keyingi qavatga product terish uchun yangilanish
                    if (!is_null($shelf->floor) && $floor_count < $shelf->floor) {
                        $floor_count++;
                        $floor_ordering = 0;
                        $shelf_size = $shelf->right_size;
                    }
                }

                if (($floor_count === 3 && $product->attribute->dioganal > 43.00) ||
                    ($floor_count === 2 && $product->attribute->dioganal > 50.00)) {
                    continue;
                }

                // Agar product sig'sa shelfga terish
                if ($product_size <= $shelf_size) {
                    $floor_ordering++;
                    $shelf_size -= $product_size;
                    $ordering_count++;

                    $shelfTemp = ProductShelfTemp::query()
                        ->where('shelf_id', $shelf->id)
                        ->where('ordering', $ordering_count);

                    $shelfTempData = [
                        'sku' => $product->sku,
                        'size' => $product->attribute->size,
                        'place' => 'right',
                        'floor' => $floor_count,
                        'floor_ordering' => $floor_ordering,
                        'is_sold' => false,
                        'sold_at' => null,
                    ];

                    if ($shelfTemp->exists()) {
                        $shelfTemp->update($shelfTempData);
                    } else {
                        ProductShelfTemp::query()->create(array_merge(['shelf_id' => $shelf->id, 'ordering' => $ordering_count], $shelfTempData));
                    }
                    $right_skus[] = $product->sku;
                }
            }
        }

        /* =========== PADDON ORDERING =========== */
        $floor_count = 1;
        $floor_ordering = 0;
        $shelf_size = $this->paddon_size;
        if ($shelf->is_paddon) {
            $stock = $this->getStock($shelf, array_merge($left_skus, $center_skus, $right_skus));

            foreach ($stock->get() as $product) {
                if (is_null($product->attribute->size)) continue;
                $product_size = $floor_ordering === 0 ? $product->attribute->size : $product->attribute->size + 15;

                if ($shelf_size < $product_size) {
                    // Keyingi paddonga product terish uchun yangilanish
                    if (!is_null($shelf->paddon_quantity) && $floor_count < $shelf->paddon_quantity) {
                        $floor_count++;
                        $floor_ordering = 0;
                        $shelf_size = $this->paddon_size;
                    }
                }

                // Agar product sig'sa shelfga terish
                if ($product_size <= $shelf_size) {
                    $floor_ordering++;
                    $shelf_size -= $product_size;
                    $ordering_count++;

                    $shelfTemp = ProductShelfTemp::query()
                        ->where('shelf_id', $shelf->id)
                        ->where('ordering', $ordering_count);

                    $shelfTempData = [
                        'sku' => $product->sku,
                        'size' => $product->attribute->size,
                        'place' => 'paddon',
                        'floor' => $floor_count,
                        'floor_ordering' => $floor_ordering,
                        'is_sold' => false,
                        'sold_at' => null,
                    ];

                    if ($shelfTemp->exists()) {
                        $shelfTemp->update($shelfTempData);
                    } else {
                        ProductShelfTemp::query()->create(array_merge(['shelf_id' => $shelf->id, 'ordering' => $ordering_count], $shelfTempData));
                    }
                    $paddon_skus[] = $product->sku;
                }
            }
        }

        $floor_count = 1;
        $floor_ordering = 0;
        $shelf_size = $this->paddon_size;
        if ($shelf->is_paddon) {
            $stock = $this->getStock($shelf, array_merge($left_skus, $center_skus, $right_skus, $paddon_skus));

            foreach ($stock->get() as $product) {
                if (is_null($product->attribute->size)) continue;
                $product_size = $floor_ordering === 0 ? $product->attribute->size : $product->attribute->size + 15;

                if ($shelf_size < $product_size) {
                    // Keyingi paddonga product terish uchun yangilanish
                    if (!is_null($shelf->paddon_quantity) && $floor_count < $shelf->paddon_quantity) {
                        $floor_count++;
                        $floor_ordering = 0;
                        $shelf_size = $this->paddon_size;
                    }
                }

                // Agar product sig'sa shelfga terish
                if ($product_size <= $shelf_size) {
                    $floor_ordering++;
                    $shelf_size -= $product_size;
                    $ordering_count++;

                    $shelfTemp = ProductShelfTemp::query()
                        ->where('shelf_id', $shelf->id)
                        ->where('ordering', $ordering_count);

                    $shelfTempData = [
                        'sku' => $product->sku,
                        'size' => $product->attribute->size,
                        'place' => 'paddon_back',
                        'floor' => $floor_count,
                        'floor_ordering' => $floor_ordering,
                        'is_sold' => false,
                        'sold_at' => null,
                    ];

                    if ($shelfTemp->exists()) {
                        $shelfTemp->update($shelfTempData);
                    } else {
                        ProductShelfTemp::query()->create(array_merge(['shelf_id' => $shelf->id, 'ordering' => $ordering_count], $shelfTempData));
                    }
                }
            }
        }

        DB::commit();

        /* =========== ORTIQCHA ROWLARNI O'CHIRISH =========== */
        $excessTemp = ProductShelfTemp::query()
            ->where('shelf_id', '=', $shelf->id)
            ->where('updated_at', '!=', now());

        if ($excessTemp->exists()) {
            $excessTemp->delete();
        }

        return ProductShelfTemp::with(['product', 'product_attr'])->where('shelf_id', $shelf->id)->get();
    }

    private function getStock($shelf, array $excludedSkus)
    {
        $products = ShelfTempService::getStocksForShelf($shelf);
        return $products
            ->whereNotIn('sku', $excludedSkus)
            ->orderByDesc('product_attributes.dioganal')
            ->select('products.*', 'product_attributes.dioganal')
            ->with('attribute');
    }
}
