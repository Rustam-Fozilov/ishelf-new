<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ManualShelf extends Model
{
    use HasFactory;
    protected $table = 'manual_shelf';
    protected $fillable = [
        'shelf_id',
        'place',
        'tip',
        'number',
        'order',
        'auto_order',
        'sku'
    ];

    public function shelf():BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    public function product():HasOne
    {
        return $this->hasOne(Product::class, 'sku', 'sku')->with('attribute');
    }
}
