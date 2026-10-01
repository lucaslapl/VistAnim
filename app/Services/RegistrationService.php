<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    /**
     * Crée une inscription en sérialisant les inscriptions concurrentes
     * sur le même événement (verrou pessimiste + transaction).
     *
     * La vérification de doublon et de capacité se fait SOUS le verrou :
     * deux soumissions simultanées ne peuvent ni créer un doublon, ni
     * dépasser la capacité.
     *
     * @param  array<string, mixed>  $data  données validées (PublicRegistrationRequest)
     * @return Registration|null null si l'email est déjà inscrit (doublon),
     *                           à rediriger vers la récupération de ticket.
     *
     * @throws ValidationException si la capacité restante est insuffisante
     */
    public function inscrire(Event $event, array $data, string $ip): ?Registration
    {
        $registration = null;
        $duplicate = false;

        DB::transaction(function () use ($event, $data, $ip, &$registration, &$duplicate) {
            Event::where('id', $event->id)->lockForUpdate()->first();

            $duplicate = Registration::where('event_id', $event->id)
                ->where('email', $data['email'])
                ->exists();

            if ($duplicate) {
                return;
            }

            $reserved = $event->fresh()->reserved_places;
            $requestedPlaces = (int) $data['nb_participants'];

            if ($event->max_participants !== null && ($reserved + $requestedPlaces) > $event->max_participants) {
                throw ValidationException::withMessages([
                    'nb_participants' => "Il n'y a plus assez de places disponibles.",
                ]);
            }

            $registration = Registration::create([
                'event_id' => $event->id,
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'nb_participants' => $requestedPlaces,
                'token' => Str::random(64),
                'consent' => true,
                'user_ip' => $ip,
                'payment_status' => $event->is_paid ? 'pending' : null,
            ]);
        });

        return $registration;
    }
}
