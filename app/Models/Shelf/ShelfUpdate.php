<?php

namespace App\Models\Shelf;

use App\Models\Product\Product;
use App\Models\User\UserShelfUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelfUpdate extends Model
{
    protected $fillable = ["shelf_id", "order", "sku"];

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    public function item()
    {
        return $this->hasMany(UserShelfUpdate::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
}
