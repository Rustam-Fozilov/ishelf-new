<?php

namespace App\Models\Shelf;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelvesTemp extends Model
{
    protected $table = 'shelves_temp';

    protected $fillable = [
        'shelf_id',
        'place',
        'floor',
        'excess'
    ];

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'shelf_id', 'id');
    }
}
