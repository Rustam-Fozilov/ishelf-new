<?php

namespace App\Models\RolePerm;

use Illuminate\Database\Eloquent\Model;

class RolePerms extends Model
{
    protected $table = 'role_perms';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'permission_id',
        'key',
        'value',
    ];
}
