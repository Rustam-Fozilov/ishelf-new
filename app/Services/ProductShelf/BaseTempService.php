<?php

namespace App\Services\ProductShelf;

use App\Models\Product\Product;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Models\Stock\StockByBranch;
use App\Services\Category\CategoryAttachService;
use App\Services\Product\ProductService;
use App\Services\Shelf\ShelfTempService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BaseTempService
{
    public static function tempAddProduct(array $data): void
    {
        $prod = ProductService::getBySku($data['sku']);
        $skus = CategoryAttachService::getAttachSku($data['shelf']->category_sku);

        if (empty($skus)) {
            $skus = [$data['shelf']['category_sku']];
        }

        if (!in_array($prod->category_sku, $skus)) throwError(__('shelf.shelf_not_match_category'));

        self::checkDublProduct($data);

        DB::beginTransaction();
        try {
            $temp = ProductShelfTemp::query()->where('id', $data['temp_id'])->first();
            if (!is_null($temp->sku)) throwError(__('shelf.product_exist'));

            $temp->update([
                'sku'     => $data['sku'],
                'is_sold' => false,
                'sold_at' => null,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            throwResponse($e);
        }
    }

    public static function checkDublProduct(array $data): void
    {
        $token = $data['shelf']->branches->token;
        $prodCount = ProductShelfTemp::query()->where('shelf_id', $data['shelf']->id)->where('sku', $data['sku'])->count();
        $stockCount = (new StockByBranch())
            ->setTable($token)
            ->newQuery()
            ->where('sku', $data['sku'])
            ->select('quantity')
            ->first();

        if (!is_null($stockCount) && $prodCount >= $stockCount->quantity) {
            throwError(__('errors.out_of_stock'));
        }
    }

    public static function deleteProductByTemp(ProductShelfTemp $temp): void
    {
        $temp->sku = null;
        $temp->is_sold = false;
        $temp->sold_at = null;
        $temp->save();
    }

    public static function tempAutoOrderProduct(Shelf $shelf, array $order_priority, array $priorityMapping): Collection
    {
        $products = ShelfTempService::getStocksForShelf($shelf);

        foreach ($order_priority as $priority) {
            $attribute = key($priority);
            $direction = $priority[$attribute];

            if (isset($priorityMapping[$attribute])) {
                [$table, $column] = $priorityMapping[$attribute];

                $products->orderBy($column, $direction);
            }
        }

        $sortedProducts = $products->get();
        $shelfTemp = ProductShelfTemp::query()->where('shelf_id', $shelf->id)->orderBy('ordering')->get();

        foreach ($shelfTemp as $index => $temp) {
            $temp->update([
                'sku'     => isset($sortedProducts[$index]) ? $sortedProducts[$index]->sku : null,
                'is_sold' => false,
                'sold_at' => null,
            ]);
        }

        return $sortedProducts;
    }

    public static function tempAutoOrderProductV2(Shelf $shelf, array $data)
    {
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
            $temp
                ->update([
                    'sku'     => isset($order_prods[$index]) ? $order_prods[$index]->sku : null,
                    'is_sold' => false,
                    'sold_at' => null,
                ]);
        }

        return $order_prods;
    }
}
