<?php

namespace App\Models\PriceTag;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTagMonthTemp extends Model
{
    protected $table = 'price_tag_month_temp';

    protected $fillable = [
        'good_id',
        'month',
        'price',
        'bonus',
    ];

    public function good(): BelongsTo
    {
        return $this->belongsTo(PriceTagGoodTemp::class, 'good_id', 'id');
    }
}
