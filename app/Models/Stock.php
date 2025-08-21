<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MongoDB\Laravel\Eloquent\Model;

class Stock extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'stocks';
    protected $fillable = [
        'product_log_id',
        'product_id',
        'branch_id',
        'branch_uid',
        'sku',
        'quantity'
    ];

    public function product():HasOne
    {
        return $this->hasOne(Product::class, 'sku', 'sku')->with('brand');
    }
    public function product_attr(): HasOne
    {
        return $this->hasOne(ProductAttribute::class,'sku','sku');
    }
}
