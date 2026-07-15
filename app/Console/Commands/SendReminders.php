<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Services\MailService;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Envoie les rappels J-1 aux participants et organisateurs (24h ± 15min avant l\'événement)';

    public function handle(): int
    {
        $now = now();

        $events = Event::with('registrations', 'organizer')
            ->where('reminder_sent', false)
            ->whereBetween('event_date', [
                $now->copy()->addHours(23)->addMinutes(45),
                $now->copy()->addHours(24)->addMinutes(15),
            ])->get();

        if ($events->isEmpty()) {
            $this->info('Aucun rappel à envoyer pour le moment.');
            return self::SUCCESS;
        }

        foreach ($events as $event) {
            $this->line("  Traitement : {$event->title}");

            MailService::sendOrganizerReminder($event, $event->registrations);
            $this->line("    ✓ Organisateur : {$event->organizer->email}");

            foreach ($event->registrations as $reg) {
                MailService::sendParticipantReminder($reg, $event);
                $this->line("    ✓ Participant : {$reg->email}");
            }

            $event->update([
                'reminder_sent' => true,
                'reminder_sent_at' => $now,
            ]);
        }

        $this->info("✓ {$events->count()} événement(s) notifié(s).");
        return self::SUCCESS;
    }
}