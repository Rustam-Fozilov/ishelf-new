<?php

namespace App\Console\Commands;

use App\Services\Upload\UploadService;
use Illuminate\Console\Command;

class DeleteInactiveUploadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-inactive-uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ishlatilmayotgan uploads tabledagi fayllarni o\'chirish';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        (new UploadService())->deleteInactiveUploads();
    }
}
