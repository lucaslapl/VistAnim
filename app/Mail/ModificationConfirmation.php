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

class ModificationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public Event $event,
        public int $oldNb,
        public ?string $refundInfo = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Modification confirmée - {$this->event->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.modification',
            with: [
                'firstname' => $this->registration->firstname,
                'event_title' => $this->event->title,
                'event_date' => $this->event->event_date,
                'old_nb' => $this->oldNb,
                'new_nb' => $this->registration->nb_participants,
                'refund_info' => $this->refundInfo,
                'token' => $this->registration->token,
            ],
        );
    }
}
