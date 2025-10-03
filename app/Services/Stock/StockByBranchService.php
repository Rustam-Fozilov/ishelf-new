<?php

namespace App\Services\Stock;

use App\Models\Stock\StockByBranch;

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
}
