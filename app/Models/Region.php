<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';

    protected $fillable = ['name_ru', 'name_uzc', 'name_uz'];

    public $timestamps = false;
}
