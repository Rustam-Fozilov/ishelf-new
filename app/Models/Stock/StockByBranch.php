<?php

namespace App\Models\Stock;

use App\Models\Product\ProductCategory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockByBranch extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'name',
        'sku',
        'brand_sku',
        'category_sku',
        'quantity',
        'product_log_id',
        'is_new',
    ];

    public function category():BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_sku', 'sku');
    }
}
