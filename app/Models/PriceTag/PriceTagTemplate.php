<?php

namespace App\Models\PriceTag;

use App\Models\PriceTag\Sennik;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceTagTemplate extends Model
{
    protected $table = 'price_tag_templates';

    protected $fillable = [
        'type',
        'name',
        'data',
        'img_count',
        'img_1_id',
        'img_2_id',
        'img_3_id',
        'month_count',
        'month_1',
        'month_2',
        'month_3',
        'show_old_price',
    ];

    protected $casts = ['show_old_price' => 'boolean', 'data' => 'json'];

    public function image_1(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'img_1_id', 'id');
    }

    public function image_2(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'img_2_id', 'id');
    }

    public function image_3(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'img_3_id', 'id');
    }

    public function senniks(): HasMany
    {
        return $this->hasMany(Sennik::class, 'template_id', 'id');
    }
}
