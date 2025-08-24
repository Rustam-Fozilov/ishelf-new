<?php

namespace App\Models\Shelf;

use App\Models\Shelf\Shelf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelfChangeItem extends Model
{
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
