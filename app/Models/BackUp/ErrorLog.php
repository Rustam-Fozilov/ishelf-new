<?php

namespace App\Models\BackUp;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $connection = 'backup';

    protected $table = 'error_logs';

    protected $fillable = [
        'error_message'
    ];
}
