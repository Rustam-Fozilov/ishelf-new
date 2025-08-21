<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parameter extends Model
{
    protected $table = 'parameters';

    protected $fillable = [
        'key',
        'category_sku',
        'name',
        'short_name',
        'ordering',
        'icon_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(ProductParameter::class, 'parameter_id', 'id');
    }

    public function icon(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'icon_id', 'id');
    }
}
