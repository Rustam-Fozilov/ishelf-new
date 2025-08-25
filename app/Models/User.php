<?php

namespace App\Models;

use App\Models\RolePerm\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'surname',
        'patronymic',
        'phone',
        'pinfl',
        'password',
        'status',
        'role_id',
        'telegraph_chat_id',
    ];

    protected $hidden = [
        'password'
    ];

    public function casts(): array
    {
        return [
            'password' => 'hashed'
        ];
    }

    public function role():BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'user_categories', 'user_id', 'category_sku', 'id', 'sku');
    }

    public function branches():BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branches', 'user_id', 'branch_id');
    }
}
