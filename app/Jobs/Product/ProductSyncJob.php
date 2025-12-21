<?php

namespace App\Jobs\Product;

use App\Services\Product\ProductService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductSyncJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'product_log';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ProductService::syncStock();
    }
}
