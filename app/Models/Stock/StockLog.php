<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $table = 'stock_logs';

    protected $fillable = [
        'branch_id',
        'quantity',
        'sku',
        'product_log_id',
    ];
}
