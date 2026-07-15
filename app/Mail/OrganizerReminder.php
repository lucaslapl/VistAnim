<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizerReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public Collection $registrations,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel J-1 : récap des inscrits pour "' . $this->event->title . '"',
        );
    }

    public function content(): Content
    {
        $totalInscrits = $this->registrations->sum('nb_participants');

        return new Content(
            view: 'emails.reminder-organisateur',
            with: [
                'organizer_name' => $this->event->organizer->name,
                'event_title' => $this->event->title,
                'event_date' => $this->event->event_date,
                'location' => $this->event->location,
                'rdv_point' => $this->event->rdv_point ?? '',
                'registrations' => $this->registrations->toArray(),
                'total_inscrits' => $totalInscrits,
                'event_id' => $this->event->id,
            ],
        );
    }
}
