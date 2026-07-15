<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'slug'];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_categories');
    }
}