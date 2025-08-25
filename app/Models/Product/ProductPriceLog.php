<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
//use Illuminate\Database\Eloquent\Model;

class ProductPriceLog extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'product_price_logs';

    protected $fillable = ['data'];
}
