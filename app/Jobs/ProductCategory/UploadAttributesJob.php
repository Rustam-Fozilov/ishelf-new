<?php

namespace App\Jobs\ProductCategory;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Product\ProductCategoryService;

class UploadAttributesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $upload_id, protected int $sku)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ProductCategoryService::runAttributeExcel($this->upload_id, $this->sku);
    }
}
