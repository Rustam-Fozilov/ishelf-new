<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Services\Shelf\PhoneShelfService;
use App\Services\Shelf\ShelfTempService;
use Illuminate\Database\Eloquent\Collection;

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
        BaseTempService::tempAddProduct($data);
    }

    public function deleteTempProduct(ProductShelfTemp $temp): void
    {
        BaseTempService::deleteProductByTemp($temp);
    }

    public function tempAutoOrderProduct(Shelf $shelf, array $priority): Collection
    {
        $priorityMapping = [
            'price' => ['products', 'products.price'],
        ];

        return BaseTempService::tempAutoOrderProduct($shelf, $priority, $priorityMapping);
    }

    public function tempAutoOrderProductV2(Shelf $shelf, array $priority)
    {
        return BaseTempService::tempAutoOrderProductV2($shelf, $priority);
    }
}
