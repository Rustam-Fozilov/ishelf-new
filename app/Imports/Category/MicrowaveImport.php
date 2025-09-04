<?php

namespace App\Imports\Category;

use App\Models\Product\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MicrowaveImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $item) {
            if ($index === 0) continue;

            $sku = $item[1];
            $sku = str_replace(["\u{A0}", " ", ","], '', $sku);

            $product = Product::query()->where('sku', $sku)->first();

            if ($product) {
                $product->attribute()->updateOrCreate(
                    [
                        'category_sku' => $product->category_sku
                    ],
                    [
                        'height' => $item[3], // Высота
                        'weight' => $item[4], // Ширина
                        'size' => $item[5], // Глубина
                    ]
                );
            }
        }
    }
}
