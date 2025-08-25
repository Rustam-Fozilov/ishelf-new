<?php

namespace App\Models\Product;

//use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model;

class ProductLog extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = ['data'];
}
