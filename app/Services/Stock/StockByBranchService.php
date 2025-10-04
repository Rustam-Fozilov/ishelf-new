<?php

namespace App\Services\Stock;

use App\Models\Branch;
use App\Models\Shelf\Shelf;
use App\Models\Product\Product;
use App\Models\Shelf\ProductShelf;
use App\Models\Stock\StockByBranch;
use App\Models\Shelf\ProductShelfTemp;
use App\Services\Category\CategoryAttachService;

class StockByBranchService
{
    public static function updateOrCreate(string $branch_uid, array $product, string $product_log_id): void
    {
        $isNew = self::checkProductForNew((int) $product['nameID'], $branch_uid);
        $stock = new StockByBranch();
        $stock
            ->setTable($branch_uid)
            ->newQuery()
            ->updateOrCreate(
                [
                    'sku'          => (int) $product['nameID'],
                    'name'         => $product['name'],
                    'brand_sku'    => (int) $product['brandID'],
                    'category_sku' => (int) $product['categoryID'],
                ],
                [
                    'is_new'         => $isNew,
                    'quantity'       => (int) $product['quantity'],
                    'product_log_id' => $product_log_id,
                ]
            );
    }

    public static function checkProductForNew(int $sku, string $branch_token): bool
    {
        $stockProduct = (new StockByBranch())
            ->setTable($branch_token)
            ->newQuery()
            ->where('sku', $sku)
            ->first();

        if (is_null($stockProduct)) return true;
        return false;
    }

    public function getStock(string $branch_token, ?int $category_sku = null)
    {
        return (new StockByBranch())
            ->setTable($branch_token)
            ->newQuery()
            ->when(!is_null($category_sku), function ($query, $category_sku) {
                $query->where('category_sku', $category_sku);
            })
            ->get();
    }

    public static function getTempProductByShelf(Shelf $shelf)
    {
        $skus = CategoryAttachService::getAttachSku($shelf->category_sku);
        $branch = Branch::query()->find($shelf->branch_id);

        if (empty($skus)) {
            $skus = [$shelf->category_sku];
        }

        $stocks = (new StockByBranch())
            ->setTable($branch->token)
            ->newQuery()
            ->whereIn('category_sku', $skus);

        $products = Product::with(['category', 'brand', 'attribute'])
            ->whereIn('sku', (clone $stocks)->pluck('sku')->toArray())
            ->where('status', 1)
            ->get();

        $productShelf = ProductShelfTemp::query()
            ->where('shelf_id', $shelf->id)
            ->get();

        $products->map(function ($product) use ($stocks, $productShelf) {
            $stock = (clone $stocks)->where('sku', (int)$product->sku)->first();
            $ordering = $productShelf->where('sku', $product->sku)->pluck('ordering')->first();
            $prodShelfCount = $productShelf->where('sku', $product->sku)->count();

            $product->quantity = $stock->quantity ?? 0;
            $product->ordering = $ordering;
            $product->case_count = $prodShelfCount;
        });

        return $products;
    }

    public static function getProductByShelf(Shelf $shelf)
    {
        $skus = CategoryAttachService::getAttachSku($shelf->category_sku);
        $branch = Branch::query()->find($shelf->branch_id);

        if (empty($skus)) {
            $skus = [$shelf->category_sku];
        }

        $stocks = (new StockByBranch())
            ->setTable($branch->token)
            ->newQuery()
            ->whereIn('category_sku', $skus);

        $products = Product::with(['category', 'brand', 'attribute'])
            ->whereIn('sku', (clone $stocks)->pluck('sku')->toArray())
            ->where('status', 1)
            ->get();

        $productShelf = ProductShelf::query()
            ->where('shelf_id', $shelf->id)
            ->get();

        $products->map(function ($product) use ($stocks, $productShelf) {
            $stock = (clone $stocks)->where('sku', (int)$product->sku)->first();
            $ordering = $productShelf->where('sku', $product->sku)->pluck('ordering')->first();
            $prodShelfCount = $productShelf->where('sku', $product->sku)->count();

            $product->quantity = $stock->quantity ?? 0;
            $product->ordering = $ordering;
            $product->case_count = $prodShelfCount;
        });

        return $products;
    }
}
