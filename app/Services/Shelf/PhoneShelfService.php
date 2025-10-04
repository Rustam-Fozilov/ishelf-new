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
                'type'          => $item['type'],
                'size'          => $item['size'] ?? null,
                'shelf_id'      => $shelf_id,
                'status_zone'   => $item['status_zone'] ?? null,
                'product_count' => $item['product_count'] ?? null,
            ]);

            if (isset($item['floor'])) {
                for ($i = 0; $i < $item['floor']; $i++) {
                    PhoneShelfItem::query()->create([
                        'size'           => $item['size'] ?? null,
                        'floor'          => $i + 1,
                        'status_zone'    => $item['status_zone'] ?? null,
                        'product_count'  => $item['product_count'] ?? null,
                        'phone_shelf_id' => $phone_shelf->id,
                    ]);
                }
            }
        }
    }

    public static function deleteOldTables(int $shelf_id): void
    {
        PhoneShelf::query()->where('shelf_id', $shelf_id)->delete();
    }

    public static function getPhoneShelfByShelfId(int $shelf_id, array|string $with = [])
    {
        return PhoneShelf::query()->where('shelf_id', $shelf_id)->with($with)->get();
    }

    public static function addStartPoint($table_id, $start_point): void
    {
        PhoneShelf::query()->where('id', $table_id)->update(['start_point' => $start_point]);
    }
}
