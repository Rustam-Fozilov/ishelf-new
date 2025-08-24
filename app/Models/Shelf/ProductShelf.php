<?php

namespace App\Models\Shelf;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Shelf\Shelf as Shelves;
use App\Models\ShelfChange;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ProductShelf extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'product_shelf';

    protected $fillable = [
        'shelf_id',
        'sku',
        'ordering',
        'place',
        'floor',
        'size',
        'floor_ordering',
        'change_id',
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

    public function change()
    {
        return $this->hasOne(ShelfChange::class, 'id', 'change_id');
    }

    public function shelves()
    {
        return $this->hasOne(Shelves::class, 'id', 'shelf_id');
    }
}
