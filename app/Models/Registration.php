<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id', 'firstname', 'lastname', 'email', 'phone',
        'nb_participants', 'token', 'consent', 'user_ip',
        'payment_status', 'payment_intent_id', 'reminder_sent',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'consent' => 'boolean',
            'nb_participants' => 'integer',
            'reminder_sent' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($registration) {
            $registration->token ??= Str::random(64);
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function scopePending($q)
    {
        $q->where('payment_status', 'pending');
    }

    public function scopePaid($q)
    {
        $q->where('payment_status', 'paid');
    }
}
