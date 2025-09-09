<?php

namespace App\Services\PriceTag;

use App\Http\Controllers\AdminController;
use App\Jobs\PriceTag\PriceTagItemJob;
use App\Models\PriceTag\PriceTagGood;
use App\Models\PriceTag\PriceTagLog;
use App\Models\PriceTag\Sennik;
use App\Models\Product\Product;

class PriceTagLogService
{
    public static function sync(): void
    {
        $log = PriceTagLog::query()->orderByDesc('id')->first();

        if ($log) {
            if (isset($log->data['params']['goods']) && !empty($log->data['params']['goods'])) {
                $sennik = Sennik::query()->create([
                    'name'       => $log->data['params']['document1с'],
                    'status'     => 0,
                    'log_id'     => $log->id,
                    'end_date'   => $log->data['params']['dataend'],
                    'start_date' => $log->data['params']['datastart'],
                ]);

                foreach ($log->data['params']['goods'] as $good) {
                    dispatch(new PriceTagItemJob($good, $sennik->id));
                }
            }
        }
    }

    public static function updateItem(array $good, int $sennik_id): void
    {
        $sku = str_replace([' ', ' '], '', $good['SKU']);
        $product = Product::query()->where('sku', $sku)->first();

        if ($product) {
            if (isset($good['retailprice'])) {
                $price = str_replace(',', '', $good['retailprice']);
                $product->price = number_format((float) $price, 2, '.', '');
                $product->save();
            }

            $prod = PriceTagGood::query()->create([
                'sku'          => $sku,
                'sennik_id'    => $sennik_id,
                'category_sku' => $product->category_sku,
            ]);

            $prev_senniks = Sennik::query()->where('id', '<', $sennik_id)->orderByDesc('id')->get();
            foreach ($prev_senniks as $prev_sennik) {
                if ($prev_sennik) PriceTagGood::query()->where('sennik_id', $prev_sennik->id)->where('sku', $sku)->delete();
                if ($prev_sennik && $prev_sennik->goods->isEmpty()) $prev_sennik->delete();
            }

            if ($prod->product->parameters->isEmpty()) {
                AdminController::syncBySku($sku);
            }

            foreach ($good['bonusandprice'] as $item) {
                $prod->months()->updateOrCreate(
                    ['month' => $item['installmentmonth'], 'type' => 'sennik'],
                    [
                        'bonus' => $item['bonus'],
                        'price' => str_replace(',', '', $item['pricediscount']),
                    ]
                );
            }
        }
    }
}
