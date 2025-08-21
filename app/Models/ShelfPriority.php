<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShelfPriority extends Model
{
    protected $fillable = ['shelf_id','order','key','order_direction','status','zone','brand_sku'];

    protected $appends = ['brand_item','zone_items'];

    public function brands()
    {
        return $this->hasMany(ShelfPriorityItem::class,'shelf_priority_id','id');

    }

    public function getBrandItemAttribute()
    {
        $item = null;
        if ($this->key === 'brand') {
            $item = $this->hasMany(ShelfPriorityItem::class,'shelf_priority_id','id')
                ->with('brand')->get();
        }

        return $item;
    }

    public function getZoneItemsAttribute()
    {
        $item = null;
        if($this->key == 'zone'){
            $item = $this->hasMany(ShelfPriorityItem::class,'shelf_priority_id','id')
                ->with('brand')->get();
        }
        return $item;
    }

    public function brand()
    {
        return $this->hasOne(CategoryBrand::class, 'brand_sku', 'brand_sku');
    }
}
