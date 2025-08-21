<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCategories extends Model
{
    protected $fillable = ['user_id','category_sku'];

    public $timestamps = false;
}
