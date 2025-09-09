<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Services\Shelf\ShelfTempService;

class GasCookersService implements ProductShelfInterface
{
    public float $default = 60;
    public float $space = 5;

    public function createTemp(Shelf $shelf): void
    {
        $ordering = 1;
        $tempService = new ShelfTempService(default: $this->default, space: $this->space);

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

        for ($i = 1; $shelf->floor + 1 > $i; $i++) {
            $ordering = $tempService->dialProduct($shelf->id, $i, 'center', $shelf->size, $ordering, 1);

            if ($shelf->left_size > 0) {
                $ordering = $tempService->dialProduct($shelf->id, $i, 'left', $shelf->left_size, $ordering, 1);
            }

            if ($shelf->right_size > 0) {
                $ordering = $tempService->dialProduct($shelf->id, $i, 'right', $shelf->right_size, $ordering, 1);
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

    public function tempAutoOrderProduct(Shelf $shelf, array $priority)
    {
        // TODO: Implement tempAutoOrderProduct() method.
    }
}
