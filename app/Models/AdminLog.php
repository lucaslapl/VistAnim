<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'details', 'ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
