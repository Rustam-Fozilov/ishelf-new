<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginTemp extends Model
{
    protected $table = 'login_temps';

    protected $fillable = [
        'user_id',
        'otp',
        'status',
        'token',
    ];
}
