<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Un admin gère tous les événements ; un organisateur uniquement les siens.
     * Couvre : modification, suppression, duplication, gestion des inscrits.
     */
    public function manage(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->organizer_id === $user->id;
    }
}
