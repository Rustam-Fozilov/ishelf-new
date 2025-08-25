<?php

namespace App\Models\PriceTag;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTagMonth extends Model
{
    protected $table = 'price_tag_months';

    protected $fillable = [
        'good_id',
        'month',
        'price',
        'bonus',
        'type',
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(PriceTagGood::class, 'good_id', 'id');
    }
}
