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

class ParticipantReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public Event $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel : demain c\'est l\'Animation "' . $this->event->title . '" !',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reminder-inscrit',
            with: [
                'firstname' => $this->registration->firstname,
                'event_title' => $this->event->title,
                'event_date' => $this->event->event_date,
                'location' => $this->event->location,
                'rdv_point' => $this->event->rdv_point ?? '',
                'event_duration' => $this->event->event_duration ?? '',
                'nb_participants' => $this->registration->nb_participants,
                'token' => $this->registration->token,
            ],
        );
    }
}
