<?php

namespace App\Models;

use App\Models\Region;
use App\Models\Shelf\Shelf;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $table = 'branches';

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
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function shelf():HasMany
    {
        return $this->hasMany(Shelf::class,'branch_id','id')->where('status',1);
    }
}
