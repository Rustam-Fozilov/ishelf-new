<?php

namespace App\Models\PriceTag;

use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceTagGoodTemp extends Model
{
    protected $table = 'price_tag_good_temp';

    protected $fillable = [
        'sennik_id',
        'sku',
        'category_sku',
    ];

    public function sennik(): BelongsTo
    {
        return $this->belongsTo(SennikTemp::class, 'sennik_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_sku', 'sku');
    }

    public function months(): HasMany
    {
        return $this->hasMany(PriceTagMonthTemp::class, 'good_id', 'id');
    }
}
