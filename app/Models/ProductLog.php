<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model;

class ProductLog extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = ['data'];
}
