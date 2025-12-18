<?php

namespace App\Jobs\PriceTag;

use Illuminate\Foundation\Queue\Queueable;
use App\Services\PriceTag\PriceTagService;
use Illuminate\Contracts\Queue\ShouldQueue;

class MoveSennikJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $sennik_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PriceTagService::moveSennik($this->sennik_id);
    }
}
