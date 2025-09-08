<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Services\Shelf\PhoneShelfService;
use App\Services\Shelf\ShelfTempService;

class VacuumCleanerService implements ProductShelfInterface
{
    public float $default = 1;

    public function createTemp(Shelf $shelf): void
    {
        $ordering = 1;
        $tempService = new ShelfTempService(default: $this->default, space: 0);

        $laptop_shelf = PhoneShelfService::getPhoneShelfByShelfId($shelf->id);
        if ($laptop_shelf->isEmpty()) throwError(__('shelf.something_went_wrong'));

        foreach ($laptop_shelf as $laptop) {
            $ordering = $tempService->dialProduct($shelf->id, $laptop->id, 'gold', $laptop->product_count, $ordering, 1, false);
        }
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
