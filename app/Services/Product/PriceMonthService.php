<?php

namespace App\Services\Product;

use App\Jobs\Product\ProductPriceMonthItemJob;
use App\Jobs\Product\ProductPriceMonthUpdateJob;
use App\Models\Product\Product;
use App\Models\Product\ProductPrice;
use App\Models\Product\ProductPriceLog;

class PriceMonthService
{
    public static function sync(): void
    {
        $log = ProductPriceLog::query()->orderByDesc('id')->first();

        if ($log) {
            foreach ($log->data as $item) {
                dispatch(new ProductPriceMonthUpdateJob($item));
            }
        }
    }

    public static function updateProductPriceMonths(array $item): void
    {
        $product_category = ProductCategoryService::firstOrCreate($item['skunamecategory'], $item['namecategory']);
        $product = Product::query()->where('sku', $item['sku'])->first() ?? new Product();

        if ($product->price !== $item['price']) {
            $product->sku = $item['sku'];
            $product->name = $item['name'];
            $product->price = $item['price'];
            $product->brand_sku = $item['skucategory'];
            $product->category_sku = $product_category->sku;
            $product->save();
        }

        foreach ($item['pricemonth'] as $price) {
            dispatch(new ProductPriceMonthItemJob($product, $price));
        }
    }

    public static function updateOrCreate($product_id, $month, $price): void
    {
        ProductPrice::query()->updateOrCreate(
            [
                'month'      => $month,
                'product_id' => $product_id,
            ],
            [
                'price' => $price,
            ]
        );
    }
}
