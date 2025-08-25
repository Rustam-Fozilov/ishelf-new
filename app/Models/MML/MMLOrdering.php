<?php

namespace App\Models\MML;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MMLOrdering extends Model
{
    protected $table = 'mml_orderings';

    protected $fillable = [
        'branch_id',
        'category_sku',
        'sku',
        'ordering',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_sku', 'sku');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
}
