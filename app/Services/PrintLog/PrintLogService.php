<?php

namespace App\Services\PrintLog;

use App\Models\PrintLog\PrintLog;
use App\Services\Shelf\ShelfChangeService;

class PrintLogService
{
    public static function create(int $shelf_id, $status, int $user_id = null): void
    {
        $last_change = ShelfChangeService::getLastChange($shelf_id);

        if (!is_null($last_change)) {
            PrintLog::query()->create([
                'shelf_id' => $shelf_id,
                'user_id' => $user_id ?? auth()->id(),
                'change_id' => $last_change->id,
                'status' => $status,
            ]);
        }
    }
}
