<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMonth extends Model
{
    protected $table = 'product_months';

    protected $fillable = [
        'sku',
        'month',
        'price',
        'bonus',
    ];
}
