<?php

namespace App\Jobs\PriceTag;

use App\Models\PriceTag\PriceTagPrints;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessPriceTagPrintJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $user_id,
        protected int $sennik_id,
        protected string $type
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PriceTagPrints::query()->create(['user_id' => $this->user_id, 'sennik_id' => $this->sennik_id, 'type' => $this->type]);
    }
}
