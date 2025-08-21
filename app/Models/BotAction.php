<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotAction extends Model
{
    use HasFactory;

    protected $table = 'bot_actions';

    protected $fillable = [
        'user_id',
        'action',
        'data',
    ];

    protected $casts = [
        'data' => 'json'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
