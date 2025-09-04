<?php

namespace App\Imports\Category;

use App\Models\Product\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class FreezerImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $item) {
            if ($index === 0) continue;

            $sku = $item[2];
            $sku = str_replace(["\u{A0}", " ", ","], '', $sku);

            $product = Product::query()->where('sku', $sku)->first();

            if ($product) {
                $product->attribute()->updateOrCreate(
                    [
                        'category_sku' => $product->category_sku
                    ],
                    [
                        'height' => $item[4], // Высота
                        'weight' => $item[5], // Ширина
                        'size' => intval($item[10]) ?? null// Длина
                    ]
                );
            }
        }
    }
}
