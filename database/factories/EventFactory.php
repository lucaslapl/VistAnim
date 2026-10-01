<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Sortie nature '.$this->faker->unique()->word(),
            'description' => 'Une balade encadrée en forêt.',
            'event_date' => now()->addDays(7)->setTime(9, 0),
            'location' => 'Forêt communale',
            'rdv_point' => 'Parking de la mairie',
            'event_duration' => '2 heures',
            'audience_type' => 'Tout public',
            'max_participants' => 20,
            'min_participants' => null,
            'is_paid' => false,
            'price_amount' => null,
            'organizer_id' => User::factory(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'is_paid' => true,
            'price_amount' => 15.00,
        ]);
    }

    public function limited(int $max = 2): static
    {
        return $this->state(fn () => ['max_participants' => $max]);
    }

    public function unlimited(): static
    {
        return $this->state(fn () => ['max_participants' => null]);
    }
}
