<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCategories extends Model
{
    protected $table = 'user_categories';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'category_sku'
    ];

}
