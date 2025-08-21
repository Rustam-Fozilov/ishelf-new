<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShelfUpdate extends Model
{
    protected $fillable = ['user_id','shelf_id','read_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }
}
