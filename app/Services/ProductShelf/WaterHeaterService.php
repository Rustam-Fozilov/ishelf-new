<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Services\Shelf\ShelfTempService;

class WaterHeaterService implements ProductShelfInterface
{
    public float $default = 1;
    public float $space = 0;

    public function createTemp(Shelf $shelf): void
    {
        $ordering = 1;
        $tempService = new ShelfTempService(default: $this->default, space: $this->space);

        for ($i = 1; 5 > $i; $i++) {
            for ($j = 1; $shelf->floor + 1 > $j; $j++) {
                $ordering = $tempService->dialProduct($shelf->id, $j, 'center', 1, $ordering, $i);
            }
        }
    }

    public function tempAddProduct(array $data): void
    {
        ShelfTempService::tempAddProduct($data);
    }

    public function deleteTempProduct(ProductShelfTemp $temp): void
    {
        ShelfTempService::deleteProductByTemp($temp);
    }

    public function tempAutoOrderProduct(Shelf $shelf, array $priority)
    {
        // TODO: Implement tempAutoOrderProduct() method.
    }
}
