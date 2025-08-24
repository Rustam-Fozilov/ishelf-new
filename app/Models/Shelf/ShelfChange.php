<?php

namespace App\Models\Shelf;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ShelfChange extends Model
{
    protected $table = 'shelf_changes';

    protected $fillable = [
        'shelf_id',
        'user_id',
        'category_sku'
    ];

    public function user_info()
    {
        return $this->hasOne(User::class,'id','user_id')->select('id','name','surname','patronymic');
    }
}
