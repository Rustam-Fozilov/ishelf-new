<?php

namespace App\Models\PrintLog;

use App\Models\Shelf\Shelf;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintLog extends Model
{
    protected $table = 'print_logs';

    protected $fillable = [
        'user_id',
        'shelf_id',
        'change_id',
        'status',
    ];

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'shelf_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
