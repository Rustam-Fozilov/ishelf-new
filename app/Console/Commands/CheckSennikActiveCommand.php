<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckSennikActiveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-sennik-active-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Sennik vaqti tugagan bo'lsa neaktiv qilish";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // TODO: sennik command qilish kerak
    }
}
