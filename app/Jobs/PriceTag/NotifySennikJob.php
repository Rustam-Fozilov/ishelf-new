<?php

namespace App\Jobs\PriceTag;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Telegraph\TelegraphService;

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
