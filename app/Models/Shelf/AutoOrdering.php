<?php

namespace App\Models\Shelf;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoOrdering extends Model
{
    protected $table = 'auto_orderings';

    protected $fillable = [
        'shelf_id',
        'order_by',
        'order_direction',
    ];

    protected function orderBy(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => json_decode($value, true),
        );
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'shelf_id', 'id');
    }
}
