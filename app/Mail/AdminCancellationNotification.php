<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminCancellationNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public Event $event,
        public bool $refunded = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Annulation d'inscription - {$this->event->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-cancellation',
            with: [
                'organizer_name' => $this->event->organizer->name,
                'event_title' => $this->event->title,
                'event_date' => $this->event->event_date,
                'firstname' => $this->registration->firstname,
                'lastname' => $this->registration->lastname,
                'nb_participants' => $this->registration->nb_participants,
                'event_id' => $this->event->id,
                'refunded' => $this->refunded,
            ],
        );
    }
}
