<?php

namespace App\Services\Product;

use App\Models\Product\ProductLog;

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
}
