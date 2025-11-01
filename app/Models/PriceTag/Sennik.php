<?php

namespace App\Models\PriceTag;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sennik extends Model
{
    protected $table = 'price_tag_senniks';

    protected $fillable = [
        'log_id',
        'name',
        'comment',
        'template_id',
        'status',
        'step',
        'start_date',
        'end_date',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PriceTagTemplate::class, 'template_id', 'id');
    }

    public function goods(): HasMany
    {
        return $this->hasMany(PriceTagGood::class, 'sennik_id', 'id');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'price_tag_branches', 'sennik_id', 'branch_id')->withTimestamps();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
