<?php

namespace App\Imports\Category;

use App\Models\Product\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class LaptopImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $item) {
            if ($index === 0 || $index == 1) continue;

            $sku = $item[1];
            $sku = str_replace(["\u{A0}", " ", ","], '', $sku);

            $product = Product::query()->where('sku', $sku)->first();
            $ram = str_replace(['GB', 'Gb', 'gb', 'MB', 'Mb', 'mb'], '', $item[16]);
            $storage = intval($item[18]);

            if ($product) {
                $product->attribute()->updateOrCreate(
                    [
                        'category_sku' => $product->category_sku
                    ],
                    [
                        'ram' => $ram, // ram
                        'storage' => $storage, // storage
                    ]
                );
            }
        }
    }
}
