<?php

namespace App\Services\PriceTag;

use App\Jobs\PriceTag\MoveSennikJob;
use App\Models\PriceTag\PriceTagGood;
use App\Models\PriceTag\PriceTagPrints;
use App\Models\PriceTag\Sennik;
use Illuminate\Pagination\LengthAwarePaginator;

class PriceTagService
{
    public function list(array $params): LengthAwarePaginator
    {
        $query = PriceTagGood::with(['product.parameters', 'product.brand', 'product.category', 'months'])
            ->whereRelation('product.category', 'sku', '=', $params['category_sku'])
            ->whereHas('product.parameters')
            ->get()
            ->groupBy('sku')
            ->map(function ($items, $sku) {
                return [
                    'sku' => $sku,
                    'name' => $items->first()->product->name,
                    'url' => $items->first()->product->url,
                    'price' => $items->first()->product->price,
                    'category' => $items->first()->product->category,
                    'brand' => $items->first()->product->brand,
                    'parameters' => $items->first()->product->parameters,
                    'prices' => $items->first()->months->map(function ($item) use ($items) {
                        return [
                            'bonus' => $item->bonus,
                            'bonus_month' => $item->month,
                            'remove_price' => $items->first()->product->price / $item->month,
                            'start_date' => $items->first()->start_date,
                            'end_date' => $items->first()->end_date,
                        ];
                    })->values(),
                ];
            })
            ->values();

        $perPage = $params['per_page'] ?? 15;
        $currentPage = $params['page'] ?? 1;
        return new LengthAwarePaginator(
            $query->forPage($currentPage, $perPage)->values(),
            $query->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );
    }

    public function print(array $params): void
    {
        foreach($params['goods'] as $sku) {
            PriceTagPrints::query()->create([
                'user_id' => auth()->id(),
                'sku' => $sku,
                'sennik_id' => $params['sennik_id'],
                'type' => 'print',
            ]);
        }
    }

    public static function checkSennikActive(): void
    {
        $senniks = Sennik::query()->where('status', 1)->where('end_date', '<', now()->subDay())->get();
        Sennik::query()
            ->where('status', 1)
            ->where('end_date', '<', now()->subDay())
            ->update(['status' => 0]);

        foreach ($senniks as $sennik) {
            dispatch(new MoveSennikJob($sennik->id));
        }
    }
}
