<?php

namespace App\Jobs\PriceTag;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\PriceTag\PriceTagLogService;

class PriceTagSyncJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PriceTagLogService::sync();
    }
}
