<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $fillable = ['user_id', 'masuk', 'keluar', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
