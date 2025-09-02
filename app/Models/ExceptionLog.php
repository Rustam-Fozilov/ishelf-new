<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExceptionLog extends Model
{
    protected $table = 'exception_logs';

    protected $fillable = ['data'];

    protected $casts = [
        'data' => 'json'
    ];
}
