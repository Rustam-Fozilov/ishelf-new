<?php

namespace App\Models\Product;

use App\Models\PriceTag\PriceTagGood;
use App\Models\Product\Product;
use App\Models\Product\ProductAttribute;
use App\Models\Shelf\Shelf;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $table = 'product_categories';

    protected $fillable = ['sku','title','skuname', 'print_type'];

    public function products():HasMany
    {
        return $this->hasMany(Product::class, 'category_sku', 'sku');
    }

    public function shelves(): HasMany
    {
        return $this->hasMany(Shelf::class, 'category_sku', 'sku');
    }

    public function users():BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_categories', 'category_sku', 'user_id');
    }
    public function filledAttr()
    {
        return $this->hasMany(ProductAttribute::class,'category_sku','sku')->where('status',1);
    }
    public function notFilledAttr()
    {
        return $this->hasMany(ProductAttribute::class,'category_sku','sku')->where('status','!=',1);
    }
    public function allAttr()
    {
        return $this->hasMany(ProductAttribute::class,'category_sku','sku');
    }

    public function price_tag_goods(): HasMany
    {
        return $this->hasMany(PriceTagGood::class, 'category_sku', 'sku');
    }
}
