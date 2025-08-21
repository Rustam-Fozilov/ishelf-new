<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTagPrints extends Model
{
    use HasFactory;

    protected $table = 'price_tag_prints';

    protected $fillable = [
        'sennik_id',
        'sku',
        'user_id',
        'type'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function sennik(): BelongsTo
    {
        return $this->belongsTo(Sennik::class, 'sennik_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
}
