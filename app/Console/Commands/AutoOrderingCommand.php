<?php

namespace App\Console\Commands;

use App\Services\Shelf\ShelfTempService;
use Illuminate\Console\Command;

class AutoOrderingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-ordering-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'shelfga avtomat tovar terildi';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        (new ShelfTempService())->makeAutoOrdering();
    }
}
