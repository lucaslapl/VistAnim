<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Notifie un participant de la suppression de son événement.
 * Ne sérialise que des scalaires : l'email reste valable même si
 * l'événement est supprimé avant que la queue ne traite l'envoi.
 */
class EventDeletionNotification extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $firstname,
        public string $eventTitle,
        public string $eventDate,
        public ?string $eventLocation,
        public bool $refunded = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Animation annulée - '.$this->eventTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-deletion',
            with: [
                'firstname' => $this->firstname,
                'event_title' => $this->eventTitle,
                'event_date' => $this->eventDate,
                'event_location' => $this->eventLocation,
                'refunded' => $this->refunded,
            ],
        );
    }
}
