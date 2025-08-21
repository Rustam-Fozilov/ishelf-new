<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchAnketa extends Model
{
    protected $connection = 'anketa';
    protected $table = 'branches';
    protected $guarded = ['id'];
}
