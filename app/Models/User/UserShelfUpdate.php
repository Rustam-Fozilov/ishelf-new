<?php

namespace App\Models\User;

use App\Models\Shelf\Shelf;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShelfUpdate extends Model
{
    protected $fillable = ['user_id', 'shelf_id', 'read_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'shelf_id', 'id');
    }
}
