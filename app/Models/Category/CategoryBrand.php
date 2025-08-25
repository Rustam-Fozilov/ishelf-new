<?php

namespace App\Models\Category;

use Illuminate\Database\Eloquent\Model;

class CategoryBrand extends Model
{
    protected $table = 'category_brands';

    protected $fillable = [
        'category_sku',
        'title',
        'brand_sku',
    ];
}
