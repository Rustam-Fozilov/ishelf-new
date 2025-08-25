<?php

namespace App\Services\Shelf;

use App\Models\Shelf\PhoneShelf;
use App\Models\Shelf\PhoneShelfItem;

class PhoneShelfService
{
    public static function create(int $shelf_id, array $items): void
    {
        self::deleteOldTables($shelf_id);

        foreach ($items as $item) {
            $phone_shelf = PhoneShelf::query()->create([
                'shelf_id' => $shelf_id,
                'status_zone' => $item['status_zone'] ?? null,
                'product_count' => $item['product_count'] ?? null,
                'type' => $item['type'],
                'size' => $item['size'] ?? null,
            ]);

            if (isset($item['floor'])) {
                for ($i = 0; $i < $item['floor']; $i++) {
                    PhoneShelfItem::query()->create([
                        'phone_shelf_id' => $phone_shelf->id,
                        'size' => $item['size'] ?? null,
                        'status_zone' => $item['status_zone'] ?? null,
                        'product_count' => $item['product_count'] ?? null,
                        'floor' => $i + 1,
                    ]);
                }
            }
        }
    }

    public static function deleteOldTables(int $shelf_id): void
    {
        PhoneShelf::query()->where('shelf_id', $shelf_id)->delete();
    }
}
