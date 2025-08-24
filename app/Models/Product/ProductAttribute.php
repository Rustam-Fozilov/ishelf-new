<?php

namespace App\Models\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    protected $fillable = [
        'sku',
        'height',
        'size',
        'status',
        'category_sku',
        'dioganal',
        'weight',
        'kv',
        'ram',
        'cpu',
        'storage'
    ];

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
}
