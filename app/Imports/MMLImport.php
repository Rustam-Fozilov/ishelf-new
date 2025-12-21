<?php

namespace App\Imports;

use App\Models\Branch;
use Illuminate\Bus\Queueable;
use App\Models\Product\Product;
use App\Models\MML\MMLOrdering;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MMLImport implements ToCollection, ShouldQueue, WithChunkReading
{
    use Queueable;

    public function collection(Collection $collection): void
    {
        $branchMap = $collection[1];

        $branches = Branch::query()
            ->select('id', 'token')
            ->whereIn('token', $branchMap)
            ->get()
            ->keyBy('token');

        $sku = $collection[3][0];
        $f_product = Product::query()->select('category_sku')->where('sku', $sku)->first();
        if ($f_product) MMLOrdering::query()->where('category_sku', $f_product->category_sku)->delete();

        foreach ($collection as $index => $item) {
            if ($index < 2) continue;

            $sku = $item[0];
            $product = Product::query()->select('category_sku')->where('sku', $sku)->first();

            if (is_null($f_product) && $product) {
                MMLOrdering::query()->where('category_sku', $product->category_sku)->delete();
                $f_product = $product;
            }

            foreach ($item->toArray() as $key => $value) {
                if ($key <= 1) continue;

                $token = $branchMap[$key] ?? null;
                $branch = $branches[$token] ?? null;

                if (!$branch) continue;

                MMLOrdering::query()->updateOrCreate(
                    ['branch_id' => $branch->id, 'sku' => $sku],
                    ['ordering' => $value, 'category_sku' => $product->category_sku ?? null]
                );
            }
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
