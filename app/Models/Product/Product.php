<?php

namespace App\Models\Product;

use App\Models\Category\CategoryBrand;
use App\Models\PriceTag\PriceTagGood;
use App\Models\Shelf\ProductShelf;
use App\Models\Stock\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'category_sku',
        'sku',
        'price',
        'name',
        'catalog_code',
        'size',
        'size_status',
        'brand_sku',
        'status',
        'url',
    ];

    public function attribute():HasOne
    {
        return $this->hasOne(ProductAttribute::class, 'sku', 'sku');
    }

    public function category():BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_sku', 'sku');
    }

    public function brand():BelongsTo
    {
        return $this->belongsTo(CategoryBrand::class, 'brand_sku', 'brand_sku');
    }

    public function stock($branch_id)
    {
        return $this->hasMany(Stock::class, 'sku', 'sku')->where('branch_id', $branch_id)->first();
    }

    public function child(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_sku', 'sku');
    }

    public function shelves(): HasMany
    {
        return $this->hasMany(ProductShelf::class, 'sku', 'sku');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'product_id', 'id');
    }

    public function price_tag_good(): HasOne
    {
        return $this->hasOne(PriceTagGood::class, 'sku', 'sku');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(ProductParameter::class, 'sku', 'sku')->orderBy('ordering');
    }
}
