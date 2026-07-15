<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventDraft;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $events = Event::withCount('registrations')
                ->with('organizer', 'categories')
                ->orderBy('event_date')
                ->get();
            $ownEvents = collect();
            $otherEvents = $events;
        } else {
            $ownEvents = Event::withCount('registrations')
                ->with('organizer', 'categories')
                ->where('organizer_id', $user->id)
                ->orderBy('event_date')
                ->get();

            $otherEvents = Event::withCount('registrations')
                ->with('organizer', 'categories')
                ->where('organizer_id', '!=', $user->id)
                ->orderBy('event_date')
                ->get();
        }

        $stats = [
            'upcoming' => Event::future()->count(),
            'participants' => Registration::whereHas('event', fn($q) => $q->future())->sum('nb_participants'),
            'structures' => User::count(),
            'past' => Event::past()->count(),
        ];

        $drafts = EventDraft::where('organizer_id', $user->id)
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.dashboard', compact('events', 'ownEvents', 'otherEvents', 'stats', 'drafts'));
    }
}