<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShelfStockPriority extends Model
{
    use HasFactory;

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
