<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => null,
            'nb_participants' => 1,
            'consent' => true,
            'user_ip' => '127.0.0.1',
            'payment_status' => null,
            'payment_intent_id' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => 'paid',
            'payment_intent_id' => 'pi_test_'.$this->faker->unique()->numerify('########'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['payment_status' => 'pending']);
    }
}
