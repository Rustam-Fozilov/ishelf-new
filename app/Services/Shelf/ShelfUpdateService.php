<?php

namespace App\Services\Shelf;

use App\Services\PrintLog\PrintLogService;

class ShelfUpdateService
{
    public static function view(int $id): void
    {
        if (auth()->user()->role->title === 'Direktor') {
            PrintLogService::create($id, 4);
        }
    }
}
