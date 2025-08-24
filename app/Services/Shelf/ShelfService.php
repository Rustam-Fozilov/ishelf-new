<?php

namespace App\Services\Shelf;

use App\Models\Shelf\Shelf;

class ShelfService
{
    public function add(array $params)
    {
        $this->checkUnique($params['branch_id'], $params['category_sku']);

        $shelf = Shelf::query()->create($params);
    }

    public function checkUnique(int $branch_id, int $category_sku)
    {
        $shelf = Shelf::query()
            ->where('branch_id', $branch_id)
            ->where('category_sku', $category_sku)
            ->first();

        if ($shelf) {
            throwError(__('shelf.exist'));
        }
    }
}
