<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhoneShelf extends Model
{
    protected $fillable = [
        'shelf_id',
        'status_zone',
        'product_count',
        'type',
        'size',
        'start_point'
    ];

    public function shelf():BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    public function phone_shelf_items(): HasMany
    {
        return $this->hasMany(PhoneShelfItem::class);
    }
}
