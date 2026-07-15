<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;

class PublicEventController extends Controller
{
    public function accueil()
    {
        $events = Event::upcoming(3)->with('organizer', 'categories')->get();

        $stats = [
            'coming_count' => Event::future()->count(),
            'past_events' => Event::past()->count(),
            'past_participants' => Registration::whereHas('event', fn($q) => $q->past())->sum('nb_participants'),
            'structures' => User::count(),
        ];

        return view('public.accueil', compact('events', 'stats'));
    }

    public function agenda()
    {
        $events = Event::future()->with('organizer', 'categories')->orderBy('event_date')->get();

        return view('public.agenda', compact('events'));
    }

    public function detail(int $id)
    {
        $event = Event::with('organizer', 'categories')->findOrFail($id);

        return view('public.detail-animation', compact('event'));
    }
}