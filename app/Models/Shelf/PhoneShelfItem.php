<?php

namespace App\Models\Shelf;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneShelfItem extends Model
{
    protected $table = 'phone_shelf_items';

    protected $fillable = [
        'phone_shelf_id',
        'size',
        'status_zone',
        'product_count',
        'floor',
    ];

    public function phone_shelf(): BelongsTo
    {
        return $this->belongsTo(PhoneShelf::class, 'phone_shelf_id', 'id');
    }
}
