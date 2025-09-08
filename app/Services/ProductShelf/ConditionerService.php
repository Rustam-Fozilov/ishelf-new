<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Services\Product\ProductService;
use App\Services\Shelf\ShelfTempService;
use Illuminate\Support\Facades\DB;

class ConditionerService implements ProductShelfInterface
{
    public float $default = 1;
    public float $space = 0;

    public function createTemp(Shelf $shelf): void
    {
        $ordering = 1;
        $tempService = new ShelfTempService(default: $this->default, space: $this->space);

        for ($i = 1; $i < 5; $i++) {
            for ($j = 1; $j < $shelf->floor + 1; $j++) {
                $ordering = $tempService->dialProduct($shelf->id, $j, 'center', 1, $ordering, $i);
            }
        }
    }

    public function tempAddProduct(array $data): void
    {
        $prod = ProductService::getBySku($data['sku']);
        if ($data['shelf']['category_sku'] !== $prod->category_sku) throwError(__('shelf.shelf_not_match_category'));

        ShelfTempService::checkDublProduct($data);

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

    public function deleteTempProduct(ProductShelfTemp $temp): void
    {
        $temp->sku = null;
        $temp->is_sold = false;
        $temp->sold_at = null;
        $temp->save();
    }

    public function tempAutoOrderProduct(Shelf $shelf, array $priority)
    {
        // TODO: Implement tempAutoOrderProduct() method.
    }
}
