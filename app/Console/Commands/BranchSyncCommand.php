<?php

namespace App\Console\Commands;

use App\Services\BranchService;
use Illuminate\Console\Command;

class BranchSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:branch-sync-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Anketadan filiallarni malumotlarini olib, bazaga yozish uchun komanda';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        BranchService::syncBranches();
    }
}
