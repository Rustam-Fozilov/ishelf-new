<?php

namespace App\Services\ProductShelf;

use App\Models\Shelf\ProductShelfTemp;
use App\Models\Stock\StockByBranch;
use App\Services\Category\CategoryAttachService;
use App\Services\Product\ProductService;
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
}
