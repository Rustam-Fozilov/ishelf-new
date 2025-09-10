<?php

namespace App\Jobs\Shelf;

use App\Models\Shelf\Shelf;
use App\Services\Telegraph\TelegraphService;
use App\Services\User\UserBranchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyShelfUpdatedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Shelf $shelf)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $regionalUsers = UserBranchService::getRegionalDirectorsByBranch($this->shelf->branch_id);
        $branchDirectors = UserBranchService::getDirectorsByBranch($this->shelf->branch_id);

        foreach ($regionalUsers as $user) {
            TelegraphService::notifyDirector($user, $this->shelf);
        }

        foreach ($branchDirectors as $user) {
            TelegraphService::notifyDirector($user, $this->shelf);
        }
    }
}
