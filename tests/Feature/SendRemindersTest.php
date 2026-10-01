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
        Mail::assertQueued(OrganizerReminder::class, fn ($mail) => $mail->hasTo($event->organizer->email));
        Mail::assertQueued(ParticipantReminder::class, fn ($mail) => $mail->hasTo($registration->email));
    }

    public function test_no_reminder_outside_window(): void
    {
        Mail::fake();
        Event::factory()->create(['event_date' => now()->addDays(3)]);
        Event::factory()->create(['event_date' => now()->addHours(2)]);

        $this->artisan('reminders:send')->assertSuccessful();

        Mail::assertNothingQueued();
        $this->assertDatabaseCount('events', 2);
        $this->assertDatabaseMissing('events', ['reminder_sent' => true]);
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
