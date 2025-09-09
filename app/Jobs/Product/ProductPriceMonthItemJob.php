<?php

namespace App\Jobs\Product;

use App\Models\Product\Product;
use App\Services\Product\PriceMonthService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProductPriceMonthItemJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Product $product, protected array $price)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PriceMonthService::updateOrCreate($this->product->id, $this->price['month'], $this->price['price']);
    }
}
