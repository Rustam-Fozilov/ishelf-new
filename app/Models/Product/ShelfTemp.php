<?php

namespace App\Models\Product;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Shelf as Shelves;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ShelfTemp extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'product_shelf_temp';

    protected $fillable = [
        'shelf_id',
        'sku',
        'ordering',
        'floor_ordering',
        'place',
        'floor',
        'size',
        'is_sold',
        'sold_at',
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'sku', 'sku')->with('brand');
    }

    public function product_attr()
    {
        return $this->hasOne(ProductAttribute::class, 'sku', 'sku');
    }

    public function shelves()
    {
        return $this->hasOne(Shelves::class, 'id', 'shelf_id');
    }
}
