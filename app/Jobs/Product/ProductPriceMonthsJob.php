<?php

namespace App\Jobs\Product;

use Illuminate\Foundation\Queue\Queueable;
use App\Services\Product\PriceMonthService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductPriceMonthsJob implements ShouldQueue
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
        PriceMonthService::sync();
    }
}
