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

class RegistrationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public Event $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Confirmation d'inscription - {$this->event->title}",
        );
    }

    public function content(): Content
    {
        $total = '';
        if ($this->event->is_paid && $this->event->price_amount) {
            $total = number_format(
                (float) $this->event->price_amount * (int) $this->registration->nb_participants,
                2, ',', ' '
            ).' €';
        }

        return new Content(
            view: 'emails.confirmation',
            with: [
                'firstname' => $this->registration->firstname,
                'event_title' => $this->event->title,
                'event_date' => $this->event->event_date,
                'location' => $this->event->location,
                'rdv_point' => $this->event->rdv_point ?? '',
                'nb_participants' => $this->registration->nb_participants,
                'token' => $this->registration->token,
                'is_paid' => $this->event->is_paid && $this->event->price_amount,
                'price' => $this->event->price_amount ?? 0,
                'payment_status' => $this->registration->payment_status ?? '',
                'total' => $total,
            ],
        );
    }
}
