<?php

namespace App\Jobs\Product;

use App\Services\Product\ProductService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProductSyncItemJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $products, protected string $log_id)
    {
        $this->queue = 'product_log';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $checkStock = validateData($this->products, [
            'StockID'                => 'required|string',
            'remainder'              => 'required|array',
            'remainder.*.name'       => 'required|string',
            'remainder.*.nameID'     => 'required',
            'remainder.*.brand'      => 'required|string',
            'remainder.*.brandID'    => 'required',
            'remainder.*.quantity'   => 'required',
            'remainder.*.category'   => 'required|string',
            'remainder.*.categoryID' => 'required',
        ]);

        if ($checkStock) {
            foreach ($this->products['remainder'] as $product) {
                ProductService::checkProduct($product, $this->products['StockID']);
            }

            ProductService::deleteOldStockSku($this->products['StockID']);
        }
    }
}
