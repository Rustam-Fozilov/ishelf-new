<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShelfManualOrdering extends Model
{
    protected $table = 'shelf_manual_ordering';
    protected $fillable = ['shelf_id', 'product_id', 'order'];
}
