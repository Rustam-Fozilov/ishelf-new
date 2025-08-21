<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShelfPriorityItem extends Model
{
    protected $fillable = ['shelf_priority_id','brand_sku','order'];
    public function brand(): HasOne
    {
        return $this->hasOne(CategoryBrand::class,'brand_sku','brand_sku');
    }
}
