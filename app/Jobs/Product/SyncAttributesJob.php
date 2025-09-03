<?php

namespace App\Jobs\Product;

use App\Models\Product\Product;
use App\Services\Product\ProductParametersService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAttributesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Product $product)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ProductParametersService::getParametersFromIdea($this->product);
    }
}
