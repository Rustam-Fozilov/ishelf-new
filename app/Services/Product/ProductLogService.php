<?php

namespace App\Services\Product;

use App\Models\PriceTag\PriceTagLog;
use App\Models\Product\ProductLog;
use App\Models\Product\ProductPriceLog;

class ProductLogService
{
    public function last(): ?ProductLog
    {
        return ProductLog::query()->orderByDesc('id')->first();
    }

    public function create(array $data)
    {
        return ProductLog::query()->create(['data' => $data]);
    }

    public function createPriceTag(array $data)
    {
        return PriceTagLog::query()->create(['data' => $data]);
    }

    public function createProductPrice(array $data)
    {
        return ProductPriceLog::query()->create(['data' => $data]);
    }

    public function deleteExcessLogs(): void
    {
        $logs_id = ProductLog::query()
            ->orderByDesc('id')
            ->limit(5)
            ->pluck('id');

        ProductLog::query()
            ->whereNotIn('id', $logs_id)
            ->delete();
    }

    public function deleteExcessPriceTagLogs(): void
    {
        $logs_id = PriceTagLog::query()
            ->orderByDesc('id')
            ->limit(5)
            ->pluck('id');

        PriceTagLog::query()
            ->whereNotIn('id', $logs_id)
            ->delete();
    }

    public function deleteExcessProductPriceLogs(): void
    {
        $keep = ProductPriceLog::query()
            ->orderByDesc('id')
            ->limit(5)
            ->pluck('id');

        ProductPriceLog::query()
            ->whereNotIn('id', $keep)
            ->delete();
    }
}
