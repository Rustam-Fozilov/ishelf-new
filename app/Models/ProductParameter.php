<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductParameter extends Model
{
    protected $table = 'product_parameters';

    protected $fillable = [
        'sku',
        'key',
        'ordering',
        'parameter_id',
        'value',
        'name_uz',
        'name_ru',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class, 'parameter_id', 'id')->with('icon');
    }
}
