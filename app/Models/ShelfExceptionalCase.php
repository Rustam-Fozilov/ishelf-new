<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShelfExceptionalCase extends Model
{
    protected $fillable = ['shelf_id', 'order', 'type', 'case'];
}
