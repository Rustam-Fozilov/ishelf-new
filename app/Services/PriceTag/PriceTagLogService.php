<?php

namespace App\Services\PriceTag;

use App\Jobs\PriceTag\PriceTagItemJob;
use App\Models\PriceTag\PriceTagLog;
use App\Models\PriceTag\Sennik;

class PriceTagLogService
{
    public static function sync(): void
    {
        $log = PriceTagLog::query()->orderByDesc('id')->first();

        if ($log) {
            if (isset($log->data['params']['goods']) && !empty($log->data['params']['goods'])) {
                $sennik = Sennik::query()->create([
                    'name'       => $log->data['params']['document1с'],
                    'status'     => 0,
                    'log_id'     => $log->id,
                    'end_date'   => $log->data['params']['dataend'],
                    'start_date' => $log->data['params']['datastart'],
                ]);

                foreach ($log->data['params']['goods'] as $good) {
                    dispatch(new PriceTagItemJob($good, $sennik->id));
                }
            }
        }
    }
}
