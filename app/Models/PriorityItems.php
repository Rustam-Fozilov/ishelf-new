<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PriorityItems extends Model
{
    protected $fillable = ['brand_sku','order','priorities_id','substitute_brand','zone'];

    public function brand():HasOne
    {
        return $this->hasOne(CategoryBrand::class,'brand_sku','brand_sku');
    }
}
