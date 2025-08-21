<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model;

class PriceTagLog extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'price_tag_logs';

    protected $fillable = ['data'];
}
