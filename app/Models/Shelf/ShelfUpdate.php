<?php

namespace App\Models\Shelf;

use App\Models\Product;
use App\Models\Shelf\Shelf;
use App\Models\UserShelfUpdate;
use Illuminate\Database\Eloquent\Model;

class ShelfUpdate extends Model
{
    protected $fillable = ["shelf_id", "order", "sku"];

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function item()
    {
        return $this->hasMany(UserShelfUpdate::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
}
