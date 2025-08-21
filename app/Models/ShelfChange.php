<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShelfChange extends Model
{
    use HasFactory;
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
