<?php

namespace App\Models\Backup;

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
