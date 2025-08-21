<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelfChangeItem extends Model
{
    use HasFactory;

    protected $table = 'shelf_change_items';

    protected $fillable = [
        'shelf_id',
        'shelf_change_id',
        'sku',
        'ordering',
        'floor_ordering',
        'place',
        'floor',
    ];

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'shelf_id', 'id');
    }
}
