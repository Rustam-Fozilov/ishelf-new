<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserBranch extends Model
{
    protected $fillable = ['user_id','branch_id'];

    public $timestamps = false;

    public function branch():HasOne
    {
        return $this->hasOne(Branch::class,'id','branch_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class,'id','user_id');
    }
}
