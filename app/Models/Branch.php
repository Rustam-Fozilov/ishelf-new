<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'status',
        'name',
        'address',
        'location',
        'token',
        'region_id',
        'phones',
        'link',
        'info'
    ];

    public function users():BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branches', 'branch_id', 'user_id');
    }

    public function region():BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function shelf():HasMany
    {
        return $this->hasMany(Shelf::class,'branch_id','id')->where('status',1);
    }
}
