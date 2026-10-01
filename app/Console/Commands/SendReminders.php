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

        // 1. Récapitulatif organisateur : une seule fois par événement,
        //    dans la fenêtre 23 h 45 – 24 h 15 avant l'événement.
        $events = Event::with('registrations', 'organizer')
            ->where('reminder_sent', false)
            ->whereBetween('event_date', [
                $now->copy()->addHours(23)->addMinutes(45),
                $now->copy()->addHours(24)->addMinutes(15),
            ])->get();

        foreach ($events as $event) {
            $this->line("  Récapitulatif organisateur : {$event->title}");

            MailService::sendOrganizerReminder($event, $event->registrations);

            $event->update([
                'reminder_sent' => true,
                'reminder_sent_at' => $now,
            ]);
        }

        // 2. Rappels participants : suivis par inscription. Les inscriptions
        //    créées après le récapitulatif reçoivent elles aussi leur rappel,
        //    jusqu'au début de l'événement.
        $participants = 0;

        Event::with(['registrations' => function ($query) {
            $query->where(function ($q) {
                $q->whereNull('payment_status')->orWhereIn('payment_status', ['pending', 'paid']);
            })->where('reminder_sent', false);
        }])
            ->whereBetween('event_date', [$now, $now->copy()->addHours(24)])
            ->get()
            ->each(function (Event $event) use (&$participants) {
                foreach ($event->registrations as $registration) {
                    MailService::sendParticipantReminder($registration, $event);
                    $registration->update(['reminder_sent' => true]);
                    $participants++;
                }
            });

        if ($events->isEmpty() && $participants === 0) {
            $this->info('Aucun rappel à envoyer pour le moment.');
        } else {
            $this->info("{$events->count()} récapitulatif(s) organisateur, {$participants} rappel(s) participant(s) envoyés.");
        }

        return self::SUCCESS;
    }
}
