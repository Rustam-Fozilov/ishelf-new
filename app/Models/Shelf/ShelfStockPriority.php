<?php

namespace App\Models\Shelf;

use App\Models\Category\CategoryBrand;
use App\Models\Product\Product;
use App\Models\Product\ProductAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShelfStockPriority extends Model
{
    protected $table = 'shelf_stock_priorities';

    protected $fillable = [
        'shelf_id',
        'sku',
        'order',
        'floor',
        'brand_sku',
    ];

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'shelf_id', 'id');
    }

    public function product()
    {
        return $this->hasOne(Product::class,'sku','sku')->with('brand');
    }

    public function product_attr(): HasOne
    {
        return $this->hasOne(ProductAttribute::class,'sku','sku');
    }

    public function brand():BelongsTo
    {
        return $this->belongsTo(CategoryBrand::class, 'brand_sku', 'brand_sku');
    }
}
