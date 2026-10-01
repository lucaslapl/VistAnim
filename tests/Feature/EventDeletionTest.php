<?php

namespace Tests\Feature;

use App\Mail\EventDeletionNotification;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Sans clé Stripe, le remboursement échoue proprement (exception
        // interceptée et journalisée) : aucun appel réseau pendant le test.
        config(['stripe.secret_key' => null]);
    }

    public function test_deleting_event_notifies_participants_and_keeps_history(): void
    {
        Mail::fake();
        $organizer = User::factory()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();
        $paid = Registration::factory()->paid()->for($event)->create();
        $free = Registration::factory()->for($event)->create();

        $this->actingAs($organizer)
            ->post("/admin/evenements/{$event->id}/supprimer")
            ->assertRedirect(route('admin.dashboard'));

        // Supprimé (soft delete) mais historique conservé pour l'audit.
        $this->assertSoftDeleted('events', ['id' => $event->id]);
        $this->assertSoftDeleted('registrations', ['id' => $paid->id]);
        $this->assertSoftDeleted('registrations', ['id' => $free->id]);

        $this->assertSame(
            $paid->payment_intent_id,
            Registration::withTrashed()->find($paid->id)->payment_intent_id,
        );

        // Chaque participant est notifié par email (queue).
        Mail::assertQueued(EventDeletionNotification::class, 2);
        Mail::assertQueued(EventDeletionNotification::class, fn ($mail) => $mail->hasTo($paid->email));
        Mail::assertQueued(EventDeletionNotification::class, fn ($mail) => $mail->hasTo($free->email));
    }

    public function test_deleted_event_disappears_but_row_remains(): void
    {
        $organizer = User::factory()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();

        $this->actingAs($organizer)
            ->post("/admin/evenements/{$event->id}/supprimer")
            ->assertRedirect(route('admin.dashboard'));

        // Plus accessible nulle part (scope global SoftDeletes)...
        $this->assertSame(0, Event::count());
        $this->get(route('animation.detail', $event->id))->assertStatus(404);

        // ...mais toujours présent pour l'historique.
        $this->assertSame(1, Event::withTrashed()->count());
    }
}
