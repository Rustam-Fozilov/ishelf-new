<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryAttach extends Model
{
    protected $table = 'category_attaches';
    protected $fillable = ['parent_sku', 'child_sku'];
}
