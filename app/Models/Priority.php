<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    protected $fillable = ['category_sku','order','key','order_direction','status','zone','brand_sku'];

    protected $appends = ['brand_item','zone_item'];

    public function getBrandItemAttribute()
    {
        $item = null;
        if ($this->key === 'brand') {
            $item = $this->hasMany(PriorityItems::class,'priorities_id','id')
                ->with('brand')->get();
        }

        return $item;
    }

    public function getZoneItemAttribute()
    {
        $item = null;
        if($this->key == 'zone'){
            $item = $this->hasMany(PriorityItems::class,'priorities_id','id')
                ->with('brand')->get();
        }
        return $item;
    }
}
