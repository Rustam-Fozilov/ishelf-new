<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\PhoneShelf;
use App\Models\Shelf\ProductShelf;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Services\Shelf\PhoneShelfService;
use App\Services\Shelf\ShelfStockPriorityService;
use App\Services\Shelf\ShelfTempService;
use Illuminate\Database\Eloquent\Collection;

class PhoneService implements ProductShelfInterface
{
    public static float $default = 1;

    public static function createTempByPhoneShelfId(int $phone_shelf_id, $ordering): int
    {
        $phone_shelf = PhoneShelf::query()->find($phone_shelf_id);
        $current_ordering = 1;

        if ($phone_shelf->status_zone == 'gold'){
            $current_ordering = self::dialProduct($phone_shelf->shelf_id, $phone_shelf->id,'gold', $phone_shelf->product_count, $ordering,1, true);
        } else if ($phone_shelf->status_zone == 'green') {
            $current_ordering = self::dialProduct($phone_shelf->shelf_id, $phone_shelf->id,'green', $phone_shelf->product_count, $ordering,1, true);
        } else if ($phone_shelf->status_zone == 'red') {
            $current_ordering = self::dialProduct($phone_shelf->shelf_id, $phone_shelf->id,'red', $phone_shelf->product_count, $ordering,1, true);
        }

        return $current_ordering;
    }

    public static function dialProduct($shelf_id, $floor, $place, $length, $ordering, $floor_ordering, $add_prod = false): int
    {
        $i = 0;

        while ($length >= self::$default) {
            $i++;

            $length = $length - self::$default;
            if ($length < 0) break;

            ShelfTempService::tempAddEmptyProduct($shelf_id, $ordering, $place, $floor, $i, self::$default);
            if ($add_prod) self::addEmptyProduct($shelf_id, $ordering, $place, $floor, $i, self::$default);

            $ordering++;
            $floor_ordering++;
        }

        return $ordering;
    }

    public static function addEmptyProduct($shelf_id, $ordering, $place, $floor, $floor_ordering, $size): void
    {
        ProductShelf::query()->create([
            'size'           => $size,
            'place'          => $place,
            'floor'          => $floor,
            'is_sold'        => false,
            'sold_at'        => null,
            'shelf_id'       => $shelf_id,
            'ordering'       => $ordering,
            'floor_ordering' => $floor_ordering,
        ]);
    }

    public function createTemp(Shelf $shelf): void
    {
        $ordering = 1;
        $phone_shelf = PhoneShelfService::getPhoneShelfByShelfId($shelf->id);
        if ($phone_shelf->isEmpty()) throwError(__('shelf.something_went_wrong'));

        $collection = collect($phone_shelf);
        $gold_phone = $collection->where('status_zone','gold')->all();

        if (!empty($gold_phone)){
            foreach ($gold_phone as $gold) {
                $ordering = $this::dialProduct($shelf->id, $gold->id, 'gold', $gold->product_count, $ordering, 1);
            }
        }

        $green_phone = $collection->where('status_zone','green')->all();
        if (!empty($green_phone)) {
            foreach ($green_phone as $green) {
                $ordering = $this::dialProduct($shelf->id, $green->id, 'green', $green->product_count, $ordering, 1);
            }
        }

        $red_phone = $collection->where('status_zone','red')->all();
        if (!empty($red_phone)) {
            foreach ($red_phone as $red) {
                $ordering = $this::dialProduct($shelf->id, $red->id, 'red', $red->product_count, $ordering, 1);
            }
        }
    }

    public function tempAddProduct(array $data): void
    {
        BaseTempService::tempAddProduct($data);
    }

    public function deleteTempProduct(ProductShelfTemp $temp): void
    {
        BaseTempService::deleteProductByTemp($temp);
    }

    public function tempAutoOrderProduct(Shelf $shelf, array $priority): Collection
    {
        $phoneShelf = PhoneShelfService::getPhoneShelfByShelfId($shelf->id);
        if ($phoneShelf->isEmpty()) throwError(__('shelf.something_went_wrong'));

        $products = ShelfTempService::getStocksForShelf($shelf);
        $phoneShelfCollection = collect($phoneShelf);

        $phoneItems = [];
        $order_priority = $priority;

        foreach ($order_priority as $key => $priority) {
            foreach ($priority as $item) {
                $products
                    ->where('products.brand_sku', '=', $item['main'])
                    ->when(isset($item['price']), function ($query) use ($item) {
                        $query->orderBy('products.price', $item['price']);
                    });

                $product_count_in_phone_shelf = $phoneShelfCollection
                    ->where('status_zone', '=', $key)
                    ->where('id', '=', $item['phone_table_id'])
                    ->pluck('product_count');

                $this->insertProducts($products, $product_count_in_phone_shelf, $shelf, $item, $key, $item['phone_table_id']);
                $phoneItems[] = ['floor' => $item['phone_table_id'], 'brand_sku' => $item['main']];
            }
        }

        ShelfStockPriorityService::createWithPhoneItems($phoneItems, $shelf->id);

        return (new ShelfTempService)->getTempByShelfId($shelf->id);
    }

    public function insertProducts($stock, $product_count_in_phone_shelf, $shelf, $direction, $place, $phone_table_id): void
    {
        $phoneShelf = PhoneShelf::query()
            ->where('shelf_id', '=', $shelf->id)
            ->where('status_zone', '=', $place)
            ->where('id', '=', $phone_table_id)
            ->pluck('id');

        if (count($stock->get()) !== 0) {
            $stock_count = count($stock->get());
            $product_count = 0;
            foreach ($product_count_in_phone_shelf as $value) {
                $product_count += intval($value);
            }

            if ($product_count <= $stock_count) {
                foreach ($stock->get() as $index => $stock2) {
                    $index++;
                    foreach ($phoneShelf as $phoneId) {
                        ProductShelfTemp::query()
                            ->where('floor_ordering', '=', $index)
                            ->where('floor', '=', $phoneId)
                            ->where('shelf_id', '=', $shelf->id)
                            ->update([
                                'place' => $place,
                                'sku' => $stock2->sku,
                                'is_sold' => false,
                                'sold_at' => null,
                            ]);
                    }
                }
            } else {
                $we_need_count = $product_count - $stock_count;
                $order_count = 0;
                $phoneShelfIndex = 0;

                foreach ($stock->get() as $stock2) {
                    $order_count++;
                    $phoneId = $phoneShelf[$phoneShelfIndex];

                    ProductShelfTemp::query()
                        ->where('floor_ordering', '=', $order_count)
                        ->where('floor', '=', $phoneId)
                        ->where('shelf_id', '=', $shelf->id)
                        ->update([
                            'place' => $place,
                            'sku' => $stock2->sku,
                            'is_sold' => false,
                            'sold_at' => null,
                        ]);
                }

                $other_products = $stock
                    ->where('products.brand_sku', '!=', $direction['main'])
                    ->orWhereIn('products.brand_sku', $direction['others'])
                    ->limit($we_need_count)
                    ->get();

                $this->updateShelf($other_products, $order_count, $phoneShelf, $phoneShelfIndex, $shelf, $place, $product_count_in_phone_shelf[$phoneShelfIndex]);
            }
        } else {
            $product_count = 0;
            foreach ($product_count_in_phone_shelf as $value) {
                $product_count += intval($value);
            }

            $stock = ShelfTempService::getStocksForShelf($shelf);

            $other_products = $stock
                ->whereIn('products.brand_sku', $direction['others'])
                ->when(isset($direction['price']), function ($query) use ($direction) {
                    $query->orderBy('products.price', $direction['price']);
                })
                ->limit($product_count)
                ->get();

            $order_count = 0;
            $phoneShelfIndex = 0;
            $this->updateShelf($other_products, $order_count, $phoneShelf, $phoneShelfIndex, $shelf, $place, $product_count_in_phone_shelf[$phoneShelfIndex]);
        }
    }

    public static function updateShelf($other_products, $order_count, $phoneShelf, $phoneShelfIndex, $shelf, $place): void
    {
        foreach ($other_products as $other_product) {
            $order_count++;
            $phoneId = $phoneShelf[$phoneShelfIndex];

            ProductShelfTemp::query()
                ->where('floor_ordering', '=', $order_count)
                ->where('floor', '=', $phoneId)
                ->where('shelf_id', '=', $shelf->id)
                ->update([
                    'place' => $place,
                    'sku' => $other_product->sku,
                    'is_sold' => false,
                    'sold_at' => null,
                ]);
        }
    }
}
