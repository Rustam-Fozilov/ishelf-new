<?php

namespace App\Jobs\Stock;

use App\Services\Telegraph\TelegraphService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendStockToBotJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected $branch,
        protected array $products,
        protected string $log_date,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        TelegraphService::notifyPM($this->branch, $this->products, $this->log_date);
    }
}
