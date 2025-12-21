<?php

namespace App\Jobs\PriceTag;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\PriceTag\PriceTagLogService;

class PriceTagItemJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $good, protected int $sennik_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $check = validateData($this->good, [
            'SKU'                              => 'required',
            'retailprice'                      => 'required',
            'bonusandprice'                    => 'required|array',
            'bonusandprice.*.bonus'            => 'required',
            'bonusandprice.*.pricediscount'    => 'required',
            'bonusandprice.*.installmentmonth' => 'required',
        ]);

        if ($check) {
            PriceTagLogService::updateItem($this->good, $this->sennik_id);
        }
    }
}
