<?php

namespace App\Models\Shelf;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Shelf\Shelf;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ProductShelfTemp extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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
        return $this->hasOne(Shelf::class, 'id', 'shelf_id');
    }
}
