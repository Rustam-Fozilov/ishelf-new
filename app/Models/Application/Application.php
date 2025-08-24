<?php

namespace App\Models\Application;

use App\Models\AppHistory;
use App\Models\Branch;
use App\Models\ProductCategory;
use App\Models\Region;
use App\Models\Shelf\Shelf;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $table = 'applications';

    protected $fillable = [
        'category_sku',
        'shelf_id',
        'step',
        'upload_id',
        'document_id',
        'owner_id',
        'comment',
        'status',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'shelf_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_sku', 'sku');
    }

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'upload_id', 'id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'document_id', 'id');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'application_branches', 'app_id', 'branch_id');
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'application_branches', 'app_id', 'region_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(AppHistory::class, 'app_id', 'id');
    }
}
