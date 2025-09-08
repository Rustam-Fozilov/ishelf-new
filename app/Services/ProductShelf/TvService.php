<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Services\Shelf\ShelfTempService;

class TvService implements ProductShelfInterface
{
    public float $default = 70.9;
    public float $space = 15;
    public float $paddon_size = 270;

    public function createTemp(Shelf $shelf): void
    {
        $ordering = 1;

        if ($shelf->is_paddon) {
            for ($i = 1; $i < $shelf->paddon_quantity + 1; $i++) {
                $ordering = $this->dialProduct($shelf->id, $i, 'paddon', $this->paddon_size, $ordering, 1);
            }

            for ($i = 1; $i < $shelf->paddon_quantity + 1; $i++) {
                $ordering = $this->dialProduct($shelf->id, $i, 'paddon_back', $this->paddon_size, $ordering, 1);
            }
        }

        for ($i = 1; $shelf->floor + 1 > $i; $i++) {
            $ordering = $this->dialProduct($shelf->id, $i, 'center', $shelf->size, $ordering, 1);
        }

        for ($i = 1; $shelf->floor_left + 1 > $i; $i++) {
            if (!is_null($shelf->left_size)) {
                $ordering = $this->dialProduct($shelf->id, $i, 'left', $shelf->left_size, $ordering, 1);
            }
        }

        for ($i = 1; $shelf->floor_right + 1 > $i; $i++) {
            if (!is_null($shelf->right_size)) {
                $ordering = $this->dialProduct($shelf->id, $i, 'right', $shelf->right_size, $ordering, 1);
            }
        }
    }

    public function dialProduct($shelf_id, $floor, $place, $length, $ordering, $floor_ordering)
    {
        $i = 0;
        $default = $this->default;
        $space = $floor_ordering == 1 ? 0 : $this->space;

        while ($length > $default + $space) {
            $i++;
            $space = $floor_ordering == 1 ? 0 : $this->space;
            $length = $length - ($default + $space);
            if ($length < 0) break;

            ShelfTempService::tempAddEmptyProduct($shelf_id, $ordering, $place, $floor, $i, $default);
            $ordering++;
            $floor_ordering++;
        }

        (new ShelfTempService())->addShelfTemp($shelf_id, $place, $floor, $length);
        return $ordering;
    }

    public function tempAddProduct(array $data): void
    {
        // TODO: Implement tempAddProduct() method.
    }

    public function deleteTempProduct(ProductShelfTemp $temp): void
    {
        // TODO: Implement deleteTempProduct() method.
    }

    public function tempAutoOrderProduct(Shelf $shelf, array $priority)
    {
        // TODO: Implement tempAutoOrderProduct() method.
    }
}
