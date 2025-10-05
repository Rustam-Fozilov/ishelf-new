<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackUp\BackUpService;

class BackUpMoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:back-up-move-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup fayllarni boshqa joyga ko\'chirish uchun ishlatiladi. Bu ishni har soatda bajaradi. Bu ishni har kuni 02:00 dan 08:00 gacha bajaradi.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        BackUpService::backUpMove();
    }
}
