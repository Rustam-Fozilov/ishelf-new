<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Services\Shelf\PhoneShelfService;
use App\Services\Shelf\ShelfTempService;
use Illuminate\Database\Eloquent\Collection;

class MicrowavesService implements ProductShelfInterface
{
    public float $default = 1;

    public function createTemp(Shelf $shelf): void
    {
        $ordering = 1;
        $tempService = new ShelfTempService(default: $this->default, space: 0);

        if ($shelf->type == 1) {
            for ($i = 1; $shelf->floor + 1 > $i; $i++) {
                if ($shelf->size > 0) {
                    $ordering = $tempService->dialProduct($shelf->id, $i,'center', $shelf->size, $ordering,1, false);
                }
            }
        } else {
            $laptop_shelf = PhoneShelfService::getPhoneShelfByShelfId($shelf->id, 'phone_shelf_items');
            if ($laptop_shelf->isEmpty()) throwError(__('shelf.something_went_wrong'));

            foreach ($laptop_shelf as $laptop) {
                if (isset($laptop->phone_shelf_items)) {
                    foreach ($laptop->phone_shelf_items as $phone_shelf_item) {
                        $ordering = $tempService->dialProduct($shelf->id, $laptop->id, $phone_shelf_item->floor ?? 'gold', $phone_shelf_item->product_count, $ordering, 1, false);
                    }
                } else {
                    $ordering = $tempService->dialProduct($shelf->id, $laptop->id, $laptop->status_zone ?? 'gold', $laptop->product_count, $ordering, 1, false);
                }
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
            'price' => ['products', 'products.price'],
        ];

        return BaseTempService::tempAutoOrderProduct($shelf, $priority, $priorityMapping);
    }
}
