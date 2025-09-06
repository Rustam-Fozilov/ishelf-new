<?php

namespace App\Services\ProductShelf;

use App\Interfaces\ProductShelfInterface;
use App\Models\Shelf\PhoneShelf;
use App\Models\Shelf\ProductShelf;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;

class PhoneService implements ProductShelfInterface
{
    public static float $default = 1;

    public static function createTempByPhoneShelfId(int $phone_shelf_id, $ordering): int
    {
        $phone_shelf = PhoneShelf::query()->find($phone_shelf_id);
        $current_ordering = 1;

        if ($phone_shelf->status_zone == 'gold'){
            $current_ordering = self::dialProduct($phone_shelf->shelf_id, $phone_shelf->id,'gold', $phone_shelf->product_count, $ordering,1, true);
        } else if ($phone_shelf->status_zone == 'green') {
            $current_ordering = self::dialProduct($phone_shelf->shelf_id, $phone_shelf->id,'green', $phone_shelf->product_count, $ordering,1, true);
        } else if ($phone_shelf->status_zone == 'red') {
            $current_ordering = self::dialProduct($phone_shelf->shelf_id, $phone_shelf->id,'red', $phone_shelf->product_count, $ordering,1, true);
        }

        return $current_ordering;
    }

    public static function dialProduct($shelf_id, $floor, $place, $length, $ordering, $floor_ordering, $add_prod = false): int
    {
        $i = 0;

        while ($length >= self::$default) {
            $i++;

            $length = $length - self::$default;
            if ($length < 0) break;

            self::tempAddEmptyProduct($shelf_id, $ordering, $place, $floor, $i, self::$default);
            if ($add_prod) self::addEmptyProduct($shelf_id, $ordering, $place, $floor, $i, self::$default);

            $ordering++;
            $floor_ordering++;
        }

        return $ordering;
    }

    public static function tempAddEmptyProduct($shelf_id, $ordering, $place, $floor, $floor_ordering, $size): void
    {
        ProductShelfTemp::query()->create([
            'size'           => $size,
            'place'          => $place,
            'floor'          => $floor,
            'is_sold'        => false,
            'sold_at'        => null,
            'shelf_id'       => $shelf_id,
            'ordering'       => $ordering,
            'floor_ordering' => $floor_ordering,
        ]);
    }

    public static function addEmptyProduct($shelf_id, $ordering, $place, $floor, $floor_ordering, $size): void
    {
        ProductShelf::query()->create([
            'size'           => $size,
            'place'          => $place,
            'floor'          => $floor,
            'is_sold'        => false,
            'sold_at'        => null,
            'shelf_id'       => $shelf_id,
            'ordering'       => $ordering,
            'floor_ordering' => $floor_ordering,
        ]);
    }

    public function createTemp(Shelf $shelf): void
    {
        // TODO: Implement createTemp() method.
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
