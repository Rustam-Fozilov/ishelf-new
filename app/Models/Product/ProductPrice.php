<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_id',
        'month',
        'price'
    ];
}
