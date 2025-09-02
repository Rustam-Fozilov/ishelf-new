<?php

namespace App\Models\Shelf;

use App\Models\Shelf\PhoneShelfItem;
use App\Models\Shelf\Shelf;
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
        return $this->belongsTo(Shelf::class, 'shelf_id', 'id');
    }

    public function phone_shelf_items(): HasMany
    {
        return $this->hasMany(PhoneShelfItem::class, 'phone_shelf_id', 'id');
    }
}
