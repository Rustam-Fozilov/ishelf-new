<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Product\Product;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Services\Shelf\ShelfTempService;
use Illuminate\Database\Eloquent\Collection;

class WashingService implements ProductShelfInterface
{
    public float $default = 60;
    public float $space = 5;

    public function createTemp(Shelf $shelf): void
    {
        $ordering = 1;
        $tempService = new ShelfTempService(default: $this->default, space: $this->space);

        for ($i = 1; $shelf->floor + 1 > $i; $i++) {
            $ordering = $tempService->dialProduct($shelf->id, $i, 'center', $shelf->size, $ordering, 1);
        }

        if ($shelf->is_paddon) {
            for ($i = 1; $i < $shelf->paddon_quantity + 1; $i++) {
                $size = ($shelf->paddon_front_quantity * $this->default) + ($shelf->paddon_front_quantity * $this->space);
                $ordering = $tempService->dialProduct($shelf->id, $i, 'paddon', $size, $ordering, 1);
            }

            for ($i = 1; $i < $shelf->paddon_quantity + 1; $i++) {
                $size = ($shelf->paddon_back_quantity * $this->default) + ($shelf->paddon_back_quantity * $this->space);
                $ordering = $tempService->dialProduct($shelf->id, $i, 'paddon_back', $size, $ordering, 1);
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
        $priorityMapping = [
            'price'  => ['products', 'products.price'],
            'weight' => ['product_attributes', 'product_attributes.weight'],
        ];

        return BaseTempService::tempAutoOrderProduct($shelf, $priority, $priorityMapping);
    }

    public function tempAutoOrderProductV2(Shelf $shelf, array $priority)
    {
        $data = $priority;
        $promo = [];
        $shelfTemp = ProductShelfTemp::query()->where('shelf_id', $shelf->id)->orderBy('ordering')->get();

        if ($data['is_promo_bank'] == 1) {
            $promo = ShelfTempService::getStocksFromPromoBank($shelf);
            $promo = collect($promo)->pluck('sku')->toArray();
        }

        $mml = ShelfTempService::getStocksFromMML($shelf);
        $mml = $mml->pluck('sku')->toArray();

        $skus = array_merge($promo, $mml);
        // skus arrayni valuelarini intga o'girish
        $skus = array_map('intval', $skus);
        // ostatkadan shu skularni olish
        $skus = ShelfTempService::checkStockForShelf($shelf, $skus, $shelfTemp->count());

        if (count($skus) < $shelfTemp->count()) {
            // agar tovar yetmayotgan bo'lsa ostatkadan olish
            $other_skus = ShelfTempService::getNeedStocks($shelf, $skus, $shelfTemp->count() - count($skus));
            $skus = array_merge($skus, $other_skus);
        }

        $order_prods = Product::query()
            ->where('products.status', 1)
            ->whereNull('products.parent_sku')
            ->whereIn('products.sku', $skus)
            ->select('products.*');

        foreach ($data['order_priority'] as $priority) {
            $key = $priority['key'];
            $direction = $priority['direction'] ?? 'asc';

            // price bo'lsa products.price ustunidan sortlash (NULL oxirida)
            if ($key === 'price') {
                $order_prods
                    ->orderByRaw('(products.price IS NULL) ASC')
                    ->orderBy('products.price', $direction);
                continue;
            }

            $alias = 'p_' . preg_replace('/[^A-Za-z0-9_]/', '_', $key);

            $order_prods
                ->leftJoin("parameters as {$alias}", function ($join) use ($alias, $key) {
                    $join->on("{$alias}.sku", '=', 'products.sku')
                        ->where("{$alias}.key", '=', $key);
                })
                // null valuelar doim oxirida kelishi uchun
                ->orderByRaw("({$alias}.value IS NULL) ASC")
                // raqamli qiymatlar uchun
                ->orderByRaw("CASE WHEN {$alias}.value REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN ({$alias}.value+0) ELSE NULL END {$direction}")
                ->orderBy("{$alias}.value", $direction);
        }

        $order_prods = $order_prods->get();
        if ($order_prods->isEmpty()) {
            throwError("Tovarlar mavjud emas");
        }

        $shelfTemp = ProductShelfTemp::query()->where('shelf_id', $shelf->id)->orderBy('ordering')->get();

        foreach ($shelfTemp as $index => $temp) {
            if ($temp->place === 'paddon' || $temp->place === 'paddon_back') {
                $find = $order_prods->where('category_sku', 2271)->first();

                if (!is_null($find)) {
                    $temp
                        ->update([
                            'sku' => $find->sku,
                            'is_sold' => false,
                            'sold_at' => null,
                        ]);
                    $order_prods = $order_prods->reject(function ($product) use ($find) {
                        return $product->sku === $find->sku;
                    })->values()->all();
                } else {
                    $temp
                        ->update([
                            'sku' => isset($order_prods[$index]) ? $order_prods[$index]->sku : null,
                            'sold_at' => null,
                        ]);
                }
            } else {
                $temp
                    ->update([
                        'sku' => isset($order_prods[$index]) ? $order_prods[$index]->sku : null,
                        'is_sold' => false,
                        'sold_at' => null,
                    ]);
            }
        }

        return $order_prods;
    }
}
