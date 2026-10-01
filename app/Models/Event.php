<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    // Cache d'instance, hors $attributes (non persisté en BDD).
    protected ?int $reservedPlacesCache = null;

    protected $fillable = [
        'title', 'description', 'event_date', 'location', 'rdv_point',
        'event_duration', 'audience_type', 'max_participants', 'min_participants',
        'is_paid', 'price_details', 'price_amount', 'image',
        'organizer_id', 'reminder_sent', 'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'is_paid' => 'boolean',
            'reminder_sent' => 'boolean',
            'price_amount' => 'decimal:2',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'event_categories');
    }

    public function scopeFuture(Builder $q): void
    {
        $q->where('event_date', '>=', now());
    }

    public function scopePast(Builder $q): void
    {
        $q->where('event_date', '<', now());
    }

    public function scopeUpcoming(Builder $q, int $limit = 3): void
    {
        $q->future()->orderBy('event_date')->limit($limit);
    }

    public function getReservedPlacesAttribute(): int
    {
        // Mis en cache par instance : les vues qui appellent plusieurs fois
        // reserved_places / remaining_places ne déclenchent plus de N+1.
        if ($this->reservedPlacesCache === null) {
            $this->reservedPlacesCache = (int) $this->registrations()
                ->where(function ($q) {
                    $q->whereNull('payment_status')
                        ->orWhereIn('payment_status', ['pending', 'paid']);
                })
                ->sum('nb_participants');
        }

        return $this->reservedPlacesCache;
    }

    public function getRemainingPlacesAttribute(): ?int
    {
        if ($this->max_participants === null) {
            return null;
        }

        return $this->max_participants - $this->reserved_places;
    }
}
