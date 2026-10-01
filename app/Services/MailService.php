<?php

namespace App\Services;

use App\Mail\AdminCancellationNotification;
use App\Mail\AdminNotification;
use App\Mail\CancellationConfirmation;
use App\Mail\EventDeletionNotification;
use App\Mail\ModificationConfirmation;
use App\Mail\OrganizerReminder;
use App\Mail\ParticipantReminder;
use App\Mail\PaymentConfirmation;
use App\Mail\RegistrationConfirmation;
use App\Mail\TicketRecovery;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public static function sendRegistrationConfirmation(Registration $registration, Event $event): void
    {
        Mail::to($registration->email)->send(new RegistrationConfirmation($registration, $event));
    }

    public static function sendPaymentConfirmation(Registration $registration, Event $event): void
    {
        Mail::to($registration->email)->send(new PaymentConfirmation($registration, $event));
    }

    public static function sendCancellationConfirmation(Registration $registration, Event $event, bool $refunded = false, bool $isAdminDeletion = false): void
    {
        Mail::to($registration->email)->send(new CancellationConfirmation($registration, $event, $refunded, $isAdminDeletion));
    }

    public static function sendTicketRecovery(Registration $registration, Event $event): void
    {
        Mail::to($registration->email)->send(new TicketRecovery($registration, $event));
    }

    public static function sendModificationConfirmation(Registration $registration, Event $event, int $oldNb, ?string $refundInfo = null): void
    {
        Mail::to($registration->email)->send(new ModificationConfirmation($registration, $event, $oldNb, $refundInfo));
    }

    public static function sendAdminNotification(Registration $registration, Event $event): void
    {
        Mail::to($event->organizer->email)->send(new AdminNotification($registration, $event));
    }

    public static function sendAdminCancellationNotification(Registration $registration, Event $event, bool $refunded = false): void
    {
        Mail::to($event->organizer->email)->send(new AdminCancellationNotification($registration, $event, $refunded));
    }

    public static function sendParticipantReminder(Registration $registration, Event $event): void
    {
        Mail::to($registration->email)->send(new ParticipantReminder($registration, $event));
    }

    public static function sendOrganizerReminder(Event $event, Collection $registrations): void
    {
        Mail::to($event->organizer->email)->send(new OrganizerReminder($event, $registrations));
    }

    /**
     * Notification de suppression d'un événement : ne transmet que des
     * scalaires au Mailable, jamais les modèles (l'événement n'existera
     * plus lorsque la queue traitera l'envoi).
     */
    public static function sendEventDeletionNotification(Registration $registration, Event $event, bool $refunded = false): void
    {
        Mail::to($registration->email)->send(new EventDeletionNotification(
            firstname: $registration->firstname,
            eventTitle: $event->title,
            eventDate: $event->event_date->format('d/m/Y à H\hi'),
            eventLocation: $event->location,
            refunded: $refunded,
        ));
    }
}
