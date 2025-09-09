<?php

namespace App\Interfaces;

use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use Illuminate\Database\Eloquent\Collection;

interface ProductShelfInterface
{
    public function createTemp(Shelf $shelf): void;

    public function tempAddProduct(array $data): void;

    public function deleteTempProduct(ProductShelfTemp $temp): void;

    public function tempAutoOrderProduct(Shelf $shelf, array $priority): Collection;
}
