<?php

namespace Tests\Feature;

use App\Mail\CancellationConfirmation;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_token_redirects_home(): void
    {
        $this->get(route('reservation.gerer', ['token' => 'invalide']))
            ->assertRedirect(route('accueil'));
    }

    public function test_reservation_page_is_displayed(): void
    {
        $registration = Registration::factory()->create();

        $this->get(route('reservation.gerer', ['token' => $registration->token]))
            ->assertOk();
    }

    public function test_cancel_keeps_registration_record_and_frees_places(): void
    {
        // Régression P0-5 : l'annulation doit conserver l'enregistrement
        // (soft delete) et donc l'historique de paiement.
        Mail::fake();
        $event = Event::factory()->limited(2)->create();
        $registration = Registration::factory()->for($event)->create(['nb_participants' => 2]);

        $this->assertSame(0, $event->fresh()->remaining_places);

        $response = $this->post(route('reservation.gerer', ['token' => $registration->token]), [
            'action' => 'cancel',
        ]);

        $response->assertOk();
        $this->assertSoftDeleted('registrations', ['id' => $registration->id]);
        $this->assertNotNull(Registration::withTrashed()->find($registration->id));
        $this->assertSame(2, $event->fresh()->remaining_places);

        Mail::assertQueued(CancellationConfirmation::class, fn ($mail) => $mail->hasTo($registration->email));
    }

    public function test_cancelled_user_can_re_register(): void
    {
        $event = Event::factory()->create();
        $registration = Registration::factory()->for($event)->create(['email' => 'alice@example.com']);
        $registration->delete();

        $this->withSession(['captcha_result' => 7, 'captcha_num1' => 3, 'captcha_num2' => 4])
            ->post(route('inscription.traiter', $event->id), [
                'firstname' => 'Alice',
                'lastname' => 'Durand',
                'email' => 'alice@example.com',
                'nb_participants' => 1,
                'consent' => '1',
                'captcha' => '7',
            ]);

        // La nouvelle inscription doit exister et être active.
        $active = Registration::where('event_id', $event->id)
            ->where('email', 'alice@example.com')
            ->whereNull('deleted_at')
            ->count();
        $this->assertSame(1, $active);
    }

    public function test_update_nb_participants_on_free_event(): void
    {
        Mail::fake();
        $event = Event::factory()->limited(5)->create();
        $registration = Registration::factory()->for($event)->create(['nb_participants' => 1]);

        $response = $this->post(route('reservation.gerer', ['token' => $registration->token]), [
            'action' => 'update',
            'nb_participants' => 3,
        ]);

        $response->assertRedirect(route('reservation.gerer', ['token' => $registration->token]));
        $this->assertSame(3, $registration->fresh()->nb_participants);
        $this->assertSame(2, $event->fresh()->remaining_places);
    }

    public function test_update_cannot_exceed_capacity(): void
    {
        $event = Event::factory()->limited(3)->create();
        $registration = Registration::factory()->for($event)->create(['nb_participants' => 1]);

        $response = $this->post(route('reservation.gerer', ['token' => $registration->token]), [
            'action' => 'update',
            'nb_participants' => 5,
        ]);

        $response->assertSessionHasErrors('nb_participants');
        $this->assertSame(1, $registration->fresh()->nb_participants);
    }
}
