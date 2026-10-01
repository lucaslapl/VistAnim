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

class PaymentConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public Event $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Paiement confirmé - {$this->event->title}",
        );
    }

    public function content(): Content
    {
        $pricePerPerson = $this->event->price_amount
            ? number_format((float) $this->event->price_amount, 2, ',', ' ').' €'
            : '0,00 €';

        $total = $this->event->price_amount
            ? number_format((float) $this->event->price_amount * (int) $this->registration->nb_participants, 2, ',', ' ').' €'
            : 'Gratuit';

        $invoiceNumber = 'FACT-'.date('Y').'-'.str_pad((string) $this->registration->id, 4, '0', STR_PAD_LEFT);

        return new Content(
            view: 'emails.payment-confirmation',
            with: [
                'firstname' => $this->registration->firstname,
                'event_title' => $this->event->title,
                'event_date' => $this->event->event_date,
                'location' => $this->event->location,
                'rdv_point' => $this->event->rdv_point ?? '',
                'nb_participants' => $this->registration->nb_participants,
                'total' => $total,
                'token' => $this->registration->token,
                'invoice_number' => $invoiceNumber,
                'payment_date' => date('d/m/Y à H\hi'),
                'payment_intent_id' => $this->registration->payment_intent_id ?? '',
                'price_per_person' => $pricePerPerson,
            ],
        );
    }
}
