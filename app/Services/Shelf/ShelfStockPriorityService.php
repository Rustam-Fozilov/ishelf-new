<?php

namespace App\Services\Shelf;

use App\Models\Shelf\ShelfStockPriority;

class ShelfStockPriorityService
{
    public function list(int $shelf_id)
    {
        return ShelfStockPriority::query()
            ->where('shelf_id', $shelf_id)
            ->with(['product.brand', 'brand'])
            ->get();
    }

    public static function add(array $params, int $shelf_id): void
    {
        if (isset($params['category_sku']) && $params['category_sku'] === 934) {
            self::createWithPhoneItems($params['items'], $shelf_id);
        } else {
            self::createWithRegularItems($params['skus'], $shelf_id);
        }
    }

    public static function delete($shelf_id): void
    {
        ShelfStockPriority::query()->where('shelf_id', $shelf_id)->delete();
    }

    public static function createWithRegularItems($skus1, $shelf_id): void
    {
        $skus = $skus1;
        usort($skus, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });
        self::delete($shelf_id);

        foreach ($skus as $item) {
            ShelfStockPriority::query()->create([
                'sku'      => $item['sku'],
                'order'    => $item['order'],
                'shelf_id' => $shelf_id,
            ]);
        }
    }

    public static function createWithPhoneItems($items, $shelf_id): void
    {
        self::delete($shelf_id);

        foreach ($items as $item) {
            ShelfStockPriority::query()->create([
                'floor'     => $item['floor'],
                'shelf_id'  => $shelf_id,
                'brand_sku' => $item['brand_sku'],
            ]);
        }
    }
}
