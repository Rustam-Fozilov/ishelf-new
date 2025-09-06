<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;

class LaptopBagService implements ProductShelfInterface
{

    public function createTemp(Shelf $shelf): void
    {
        // TODO: Implement createTemp() method.
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
