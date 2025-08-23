<?php

namespace App\Models\RolePerm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['title', 'color','status','category_must_be_added'];

    public function users():HasMany
    {
        return $this->hasMany(User::class);
    }
}
