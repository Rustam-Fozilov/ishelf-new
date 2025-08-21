<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class StockByBranch extends Model
{
    use HasFactory;

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
