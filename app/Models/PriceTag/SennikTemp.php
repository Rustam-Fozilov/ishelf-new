<?php

namespace App\Models\PriceTag;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SennikTemp extends Model
{
    protected $table = 'price_tag_sennik_temp';

    protected $fillable = [
        'log_id',
        'name',
        'template_id',
        'start_date',
        'end_date',
        'branch_id',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PriceTagTemplate::class, 'template_id', 'id');
    }

    public function goods(): HasMany
    {
        return $this->hasMany(PriceTagGoodTemp::class, 'sennik_id', 'id');
    }
}
