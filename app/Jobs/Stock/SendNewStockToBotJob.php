<?php

namespace App\Jobs\Stock;

use App\Services\Telegraph\TelegraphService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNewStockToBotJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected $branch,
        protected array $products,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        TelegraphService::notifyNewProduct($this->products, $this->branch);
    }
}
