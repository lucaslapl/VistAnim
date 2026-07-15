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

class CancellationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public Event $event,
        public bool $refunded = false,
        public bool $isAdminDeletion = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Annulation confirmée - {$this->event->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cancellation',
            with: [
                'firstname' => $this->registration->firstname,
                'event_title' => $this->event->title,
                'event_date' => $this->event->event_date,
                'refunded' => $this->refunded,
                'isAdminDeletion' => $this->isAdminDeletion,
            ],
        );
    }
}
