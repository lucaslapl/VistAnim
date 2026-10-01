<?php

namespace Tests\Feature;

use App\Mail\AdminNotification;
use App\Mail\RegistrationConfirmation;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    private array $captchaSession = [
        'captcha_result' => 7,
        'captcha_num1' => 3,
        'captcha_num2' => 4,
    ];

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice@example.com',
            'phone' => '0601020304',
            'nb_participants' => 1,
            'consent' => '1',
            'captcha' => '7',
        ], $overrides);
    }

    public function test_inscription_form_is_displayed(): void
    {
        $event = Event::factory()->create();

        $this->get(route('inscription.form', $event->id))->assertOk();
    }

    public function test_full_event_redirects_to_agenda(): void
    {
        $event = Event::factory()->limited(2)->create();
        Registration::factory()->for($event)->create(['nb_participants' => 2]);

        $this->get(route('inscription.form', $event->id))
            ->assertRedirect(route('agenda'));
    }

    public function test_free_registration_succeeds_and_sends_emails(): void
    {
        Mail::fake();
        $event = Event::factory()->create();

        $response = $this->withSession($this->captchaSession)
            ->post(route('inscription.traiter', $event->id), $this->payload());

        $registration = Registration::where('event_id', $event->id)->first();
        $this->assertNotNull($registration);
        $response->assertRedirect(route('confirmation', ['token' => $registration->token]));

        Mail::assertQueued(RegistrationConfirmation::class, fn ($mail) => $mail->hasTo('alice@example.com'));
        Mail::assertQueued(AdminNotification::class, fn ($mail) => $mail->hasTo($event->organizer->email));
    }

    public function test_registration_exceeding_capacity_returns_places_error(): void
    {
        // Régression P0-1 : l'erreur de capacité levée dans la transaction
        // doit remonter sur nb_participants (et non un message générique).
        $event = Event::factory()->limited(2)->create();
        Registration::factory()->for($event)->create(['nb_participants' => 1]);

        $response = $this->withSession($this->captchaSession)
            ->post(route('inscription.traiter', $event->id), $this->payload(['nb_participants' => 2]));

        $response->assertSessionHasErrors('nb_participants');
        $response->assertSessionDoesntHaveErrors('error');
        $this->assertSame(1, Registration::where('event_id', $event->id)->count());
    }

    public function test_wrong_captcha_rejects_registration(): void
    {
        $event = Event::factory()->create();

        $response = $this->withSession($this->captchaSession)
            ->post(route('inscription.traiter', $event->id), $this->payload(['captcha' => '999']));

        $response->assertSessionHasErrors('captcha');
        $this->assertDatabaseMissing('registrations', ['event_id' => $event->id]);
    }

    public function test_duplicate_email_redirects_to_ticket_recovery(): void
    {
        $event = Event::factory()->create();
        Registration::factory()->for($event)->create(['email' => 'alice@example.com']);

        $response = $this->withSession($this->captchaSession)
            ->post(route('inscription.traiter', $event->id), $this->payload());

        $response->assertRedirect(route('ticket.recuperer', ['event_id' => $event->id]));
    }

    public function test_paid_event_creates_pending_registration(): void
    {
        Mail::fake();
        $event = Event::factory()->paid()->create();

        $response = $this->withSession($this->captchaSession)
            ->post(route('inscription.traiter', $event->id), $this->payload());

        $registration = Registration::where('event_id', $event->id)->first();
        $this->assertNotNull($registration);
        $this->assertSame('pending', $registration->payment_status);
        $response->assertRedirect(route('paiement', ['token' => $registration->token]));
    }

    public function test_honeypot_silently_redirects_home(): void
    {
        $event = Event::factory()->create();

        $this->withSession($this->captchaSession)
            ->post(route('inscription.traiter', $event->id), $this->payload(['website' => 'http://spam.example']))
            ->assertRedirect(route('accueil'));

        $this->assertDatabaseMissing('registrations', ['event_id' => $event->id]);
    }

    public function test_cannot_register_for_past_event(): void
    {
        // Régression P1 : pas d'inscription à un événement passé, même en
        // connaissant l'URL directe.
        $event = Event::factory()->create(['event_date' => now()->subDays(2)]);

        $this->get(route('inscription.form', $event->id))
            ->assertRedirect(route('agenda'));

        $this->withSession($this->captchaSession)
            ->post(route('inscription.traiter', $event->id), $this->payload())
            ->assertRedirect(route('agenda'));

        $this->assertDatabaseMissing('registrations', ['event_id' => $event->id]);
    }

    public function test_ip_rate_limited_after_three_registrations(): void
    {
        $event = Event::factory()->unlimited()->create();

        foreach ([1, 2, 3] as $i) {
            $this->withSession($this->captchaSession)
                ->post(route('inscription.traiter', $event->id), $this->payload(['email' => "user{$i}@example.com"]));
        }

        $response = $this->withSession($this->captchaSession)
            ->post(route('inscription.traiter', $event->id), $this->payload(['email' => 'user4@example.com']));

        $response->assertSessionHasErrors('email');
        $this->assertSame(3, Registration::where('event_id', $event->id)->count());
    }
}
