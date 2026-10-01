<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventDraft extends Model
{
    protected $fillable = ['organizer_id', 'draft_label', 'draft_data'];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
        ];
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
}
