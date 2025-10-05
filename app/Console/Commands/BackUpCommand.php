<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackUp\BackUpService;

class BackUpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bir kunda bir marta backup olish uchun';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        BackUpService::backUp();
    }
}
