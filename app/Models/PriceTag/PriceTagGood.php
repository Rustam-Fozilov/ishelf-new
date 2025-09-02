<?php

namespace App\Models\PriceTag;

use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductMonth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceTagGood extends Model
{
    protected $table = 'price_tag_goods';

    protected $fillable = [
        'sennik_id',
        'sku',
        'category_sku',
        'branch_token',
    ];

    public function sennik(): BelongsTo
    {
        return $this->belongsTo(Sennik::class, 'sennik_id', 'id');
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
        return $this->hasMany(PriceTagMonth::class, 'good_id', 'id');
    }

    public function months_info(): HasMany
    {
        return $this->hasMany(ProductMonth::class, 'sku', 'sku');
    }
}
