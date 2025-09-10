<?php

namespace App\Services\Shelf;

use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Models\Shelf\ShelfChange;
use App\Models\Shelf\ShelfChangeItem;

class ShelfChangeService
{
    public static function getLastChange(int $shelf_id, $with = []): ?ShelfChange
    {
        return ShelfChange::with($with)
            ->where('shelf_id', $shelf_id)
            ->orderBy('id', 'desc')
            ->first();
    }

    public static function create(Shelf $shelf, int $user_id = null): ShelfChange
    {
        $change = ShelfChange::query()->create([
            'user_id'      => $user_id ?? auth()->id(),
            'shelf_id'     => $shelf->id,
            'category_sku' => $shelf->category_sku,
        ]);

        $shelfTemp = ProductShelfTemp::query()->whereNotNull('sku')->where('shelf_id', $shelf->id)->get();
        foreach ($shelfTemp as $temp) {
            ShelfChangeItem::query()->create([
                'sku'             => $temp->sku,
                'place'           => $temp->place,
                'floor'           => $temp->floor,
                'shelf_id'        => $shelf->id,
                'ordering'        => $temp->ordering,
                'floor_ordering'  => $temp->floor_ordering,
                'shelf_change_id' => $change->id,
            ]);
        }

        return $change;
    }
}
