<?php

namespace App\Imports\Product;

use App\Models\Product\Parameter;
use App\Models\Product\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ParameterImport implements ToCollection, ShouldQueue, WithChunkReading
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $params = [];

        foreach ($collection as $index => $item) {
            if ($index === 0 && empty($params)) {
                $params = $item;
                continue;
            }

            $sku = str_replace([' ', ' ', '.', ','], '', $item[0]);
            unset($item[0]);

            $product = Product::query()->where('sku', $sku)->first();
            if (!$product) continue;

            foreach ($item as $idx => $value) {
                $slug = Str::slug($params[$idx], '_');

                Parameter::query()->updateOrCreate(
                    [
                        'category_sku' => $product->category_sku,
                        'sku' => $sku,
                        'key' => $slug,
                        'name' => $params[$idx],
                        'type' => 'excel'
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
