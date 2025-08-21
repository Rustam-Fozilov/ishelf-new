<?php

namespace App\Models;

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
