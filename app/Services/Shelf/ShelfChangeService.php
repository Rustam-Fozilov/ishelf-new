<?php

namespace App\Services\Shelf;

use App\Models\Shelf\ShelfChange;

class ShelfChangeService
{
    public static function getLastChange(int $shelf_id, $with = []): ?ShelfChange
    {
        return ShelfChange::query()
            ->where('shelf_id', $shelf_id)
            ->orderBy('id', 'desc')
            ->with($with)
            ->first();
    }
}
