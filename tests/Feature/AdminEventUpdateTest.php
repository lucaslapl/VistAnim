<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function updatePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Balade nocturne',
            'description' => 'Une sortie en forêt',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'location' => 'Forêt communale',
            'rdv_point' => 'Parking de la mairie',
            'event_duration' => '2 heures',
            'audience_type' => 'Tout public',
            'max_participants' => 10,
            'is_paid' => false,
            'categories' => [],
        ], $overrides);
    }

    public function test_organizer_can_update_own_event(): void
    {
        $organizer = User::factory()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();

        $this->actingAs($organizer)
            ->post("/admin/evenements/{$event->id}/modifier", $this->updatePayload())
            ->assertRedirect(route('admin.evenements.modifier', $event->id));

        $this->assertSame('Balade nocturne', $event->fresh()->title);
    }

    public function test_capacity_cannot_drop_below_reserved_places(): void
    {
        $organizer = User::factory()->create();
        $event = Event::factory()->for($organizer, 'organizer')->limited(5)->create();
        Registration::factory()->for($event)->create(['nb_participants' => 3]);

        $response = $this->actingAs($organizer)
            ->post("/admin/evenements/{$event->id}/modifier", $this->updatePayload(['max_participants' => 2]));

        $response->assertSessionHasErrors('max_participants');
        $this->assertSame(5, $event->fresh()->max_participants);
    }

    public function test_event_date_cannot_be_moved_to_the_past(): void
    {
        $organizer = User::factory()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();

        $response = $this->actingAs($organizer)
            ->post("/admin/evenements/{$event->id}/modifier", $this->updatePayload([
                'event_date' => now()->subDay()->format('Y-m-d'),
            ]));

        $response->assertSessionHasErrors('event_date');
    }

    public function test_registration_must_belong_to_event_in_url(): void
    {
        // Régression P1 : un organisateur ne doit pas pouvoir manipuler une
        // inscription étrangère via une URL croisée (son eventId + l'id d'une
        // inscription d'un autre événement).
        $organizer = User::factory()->create();
        $ownEvent = Event::factory()->for($organizer, 'organizer')->create();
        $foreignEvent = Event::factory()->create();
        $registration = Registration::factory()->for($foreignEvent)->create();

        $this->actingAs($organizer)
            ->get("/admin/inscriptions/{$ownEvent->id}/modifier/{$registration->id}")
            ->assertStatus(404);

        $this->actingAs($organizer)
            ->post("/admin/inscriptions/{$ownEvent->id}/modifier/{$registration->id}", [
                'firstname' => 'Intrus',
                'lastname' => 'Test',
                'email' => 'intrus@example.com',
                'nb_participants' => 1,
            ])
            ->assertStatus(404);

        $this->assertSame($foreignEvent->id, $registration->fresh()->event_id);
        $this->assertNotSame('intrus@example.com', $registration->fresh()->email);
    }
}
