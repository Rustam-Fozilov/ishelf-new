<?php

namespace App\Models;

use App\Models\Product\Shelf as ProductShelf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class Shelf extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'branch_id',
        'category_sku',
        'type',
        'floor',
        'size',
        'is_paddon',
        'paddon_quantity',
        'paddon_front_quantity',
        'paddon_back_quantity',
        'paddon_size',
        'left_size',
        'right_size',
        'product_count',
        'upload_id',
        'status',
    ];

    public function branches():BelongsTo
    {
        return $this->belongsTo(Branch::class,'branch_id','id');
    }

    public function category():BelongsTo
    {
        return $this->belongsTo(ProductCategory::class,'category_sku','sku');
    }

    public function phone_tables():HasMany
    {
        return $this->hasMany(PhoneShelf::class);
    }

    public function manual_orders():HasMany
    {
        return $this->hasMany(ManualShelf::class);
    }

    public function getTotal()
    {
        return Stock::query()->where('stocks.branch_id', '=', $this->branch_id)
            ->join('products', function ($p) {
                $p->on('products.sku', '=', 'stocks.sku')
                    ->where('products.category_sku', '=', $this->category_sku);
            })->count();
    }

    public function updates():HasMany
    {
        return $this->hasMany(ShelfUpdate::class);
    }

    public function user_updates():HasMany
    {
        return $this->hasMany(UserShelfUpdate::class)->where('user_id', Auth::id())->whereNull('read_at');
    }
    public function product_shelf()
    {
        return $this->hasMany(ProductShelf::class,'shelf_id','id');
    }
    public function last_change()
    {
        return $this->hasOne(ShelfChange::class,'shelf_id','id')->orderBy('created_at','desc');
    }

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class,'upload_id','id');
    }

    public function auto_ordering(): HasOne
    {
        return $this->hasOne(AutoOrdering::class, 'shelf_id', 'id');
    }
}
