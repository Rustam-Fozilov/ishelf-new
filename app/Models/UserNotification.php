<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'notification_id', 'readed_at'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
