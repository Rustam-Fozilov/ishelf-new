<?php

namespace App\Jobs\Product;

use Illuminate\Foundation\Queue\Queueable;
use App\Services\Product\PriceMonthService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductPriceMonthUpdateJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $good)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $checkStock = validateData($this->good, [
            'sku'                => 'required',
            'name'               => 'required|string',
            'price'              => 'required',
            'skucategory'        => 'required',
            'namecategory'       => 'required|string',
            'skunamecategory'    => 'required',
            'pricemonth'         => 'required|array',
            'pricemonth.*.month' => 'required',
            'pricemonth.*.price' => 'required',
        ]);

        if ($checkStock) {
            PriceMonthService::updateProductPriceMonths($this->good);
        }
    }
}
