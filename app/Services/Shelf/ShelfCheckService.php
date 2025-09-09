<?php

namespace App\Services\Shelf;

use App\Models\Shelf\Shelf;

class ShelfCheckService
{
    public function __construct(
        protected ?Shelf $shelf = null
    )
    {
    }

    public function checkUnique(int $branch_id, int $category_sku): void
    {
        $shelf = Shelf::query()
            ->where('branch_id', $branch_id)
            ->where('category_sku', $category_sku)
            ->where('status', 1)
            ->first();

        if ($shelf) {
            throwError(__('shelf.exist'));
        }
    }

    public function isPhone(int $sku): bool
    {
        $skus = [934, 438, 30, 592, 49, 351, 1181];

        if (in_array($sku, $skus)) {
            return true;
        }

        return false;
    }
}
