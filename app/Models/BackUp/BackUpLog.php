<?php

namespace App\Models\BackUp;

use Illuminate\Database\Eloquent\Model;

class BackUpLog extends Model
{
    protected $connection = 'backup';

    protected $table = 'back_up_logs';

    protected $fillable = [
        'filename',
        'path',
        'project',
        'status'
    ];
}
