<?php

namespace App\Services\ProductShelf;

use App\Models\Shelf\Shelf;
use App\Models\Shelf\ProductShelfTemp;
use App\Services\Shelf\ShelfTempService;
use App\Interfaces\ProductShelfInterface;
use Illuminate\Database\Eloquent\Collection;

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
        BaseTempService::tempAddProduct($data);
    }

    public function deleteTempProduct(ProductShelfTemp $temp): void
    {
        BaseTempService::deleteProductByTemp($temp);
    }

    public function tempAutoOrderProduct(Shelf $shelf, array $priority): Collection
    {
        $priorityMapping = [
            'kv'    => ['product_attributes', 'product_attributes.kv'],
            'price' => ['products', 'products.price'],
        ];

        return BaseTempService::tempAutoOrderProduct($shelf, $priority, $priorityMapping);
    }
}
