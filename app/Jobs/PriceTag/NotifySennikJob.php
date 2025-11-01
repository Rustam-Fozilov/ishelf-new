<?php

namespace App\Jobs\PriceTag;

use App\Services\Telegraph\TelegraphService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifySennikJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $user_id, protected $sennik_ids)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        TelegraphService::notifyNewSennik($this->user_id, $this->sennik_ids);
    }
}
