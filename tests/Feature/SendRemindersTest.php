<?php

namespace Tests\Feature;

use App\Mail\OrganizerReminder;
use App\Mail\ParticipantReminder;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminders_are_sent_for_events_within_window(): void
    {
        Mail::fake();
        $event = Event::factory()->create(['event_date' => now()->addHours(24)]);
        $registration = Registration::factory()->for($event)->create();

        $this->artisan('reminders:send')->assertSuccessful();

        $this->assertTrue($event->fresh()->reminder_sent);
        $this->assertTrue($registration->fresh()->reminder_sent);
        Mail::assertQueued(OrganizerReminder::class, fn ($mail) => $mail->hasTo($event->organizer->email));
        Mail::assertQueued(ParticipantReminder::class, fn ($mail) => $mail->hasTo($registration->email));
    }

    public function test_no_reminder_more_than_24h_before_event(): void
    {
        Mail::fake();
        Event::factory()->create(['event_date' => now()->addDays(3)]);
        $registration = Registration::factory()->create();

        $this->artisan('reminders:send')->assertSuccessful();

        Mail::assertNothingQueued();
        $this->assertFalse($registration->fresh()->reminder_sent);
    }

    public function test_participant_reminder_sent_even_shortly_before_event(): void
    {
        // Un événement à 2 h ne déclenche pas le récapitulatif organisateur
        // (fenêtre 23h45–24h15 passée), mais le participant doit être rappelé.
        Mail::fake();
        $event = Event::factory()->create(['event_date' => now()->addHours(2)]);
        $registration = Registration::factory()->for($event)->create();

        $this->artisan('reminders:send')->assertSuccessful();

        Mail::assertNotQueued(OrganizerReminder::class);
        Mail::assertQueued(ParticipantReminder::class, fn ($mail) => $mail->hasTo($registration->email));
        $this->assertFalse($event->fresh()->reminder_sent);
        $this->assertTrue($registration->fresh()->reminder_sent);
    }

    public function test_late_registration_still_gets_reminder(): void
    {
        // Régression P2 : une inscription créée après l'envoi du récapitulatif
        // doit quand même recevoir son rappel.
        Mail::fake();
        $event = Event::factory()->create(['event_date' => now()->addHours(24)]);
        Registration::factory()->for($event)->create();

        $this->artisan('reminders:send')->assertSuccessful();
        Mail::assertQueuedCount(2);

        $late = Registration::factory()->for($event)->create();

        $this->artisan('reminders:send')->assertSuccessful();

        Mail::assertQueuedCount(3);
        Mail::assertQueued(ParticipantReminder::class, fn ($mail) => $mail->hasTo($late->email));
    }

    public function test_expired_registration_is_not_reminded(): void
    {
        Mail::fake();
        $event = Event::factory()->create(['event_date' => now()->addHours(2)]);
        Registration::factory()->for($event)->create(['payment_status' => 'expired']);

        $this->artisan('reminders:send')->assertSuccessful();

        Mail::assertNotQueued(ParticipantReminder::class);
    }

    public function test_reminder_sent_only_once(): void
    {
        Mail::fake();
        $event = Event::factory()->create(['event_date' => now()->addHours(24)]);
        Registration::factory()->for($event)->create();

        $this->artisan('reminders:send')->assertSuccessful();
        Mail::assertQueuedCount(2); // 1 organisateur + 1 participant

        $this->artisan('reminders:send')->assertSuccessful();
        Mail::assertQueuedCount(2); // aucun doublon
    }
}
