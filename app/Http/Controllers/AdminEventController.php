<?php

namespace App\Http\Controllers;

use App\Models\AdminLog;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventDraft;
use App\Services\MailService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminEventController extends Controller
{
    public function creer(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $draftData = session('draft_data');
        $draftId = session('draft_id');

        return view('admin.creer-evenement', compact('categories', 'draftData', 'draftId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_dates' => 'required|array|min:1',
            'event_dates.*' => 'date|after_or_equal:today',
            'location' => 'nullable|string|max:200',
            'rdv_point' => 'nullable|string|max:200',
            'event_duration' => 'nullable|string|max:100',
            'audience_type' => 'nullable|string|max:100',
            'max_participants' => 'nullable|integer|min:1',
            'min_participants' => 'nullable|integer|min:0',
            'is_paid' => 'boolean',
            'price_details' => 'nullable|string',
            'price_amount' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($request->filled('min_participants') && $request->filled('max_participants')
            && $request->integer('min_participants') > $request->integer('max_participants')) {
            return back()->withErrors(['min_participants' => 'Le minimum ne peut pas dépasser le maximum.'])
                ->withInput();
        }

        if ($request->boolean('is_paid') && $request->float('price_amount') <= 0) {
            return back()->withErrors(['price_amount' => 'Le montant doit être supérieur à 0 pour un événement payant.'])
                ->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage($request->file('image'));
        }

        $createdEvents = [];
        DB::transaction(function () use ($request, $imagePath, &$createdEvents) {
            foreach ($request->input('event_dates') as $date) {
                $event = Event::create([
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'event_date' => $date,
                    'location' => $request->input('location'),
                    'rdv_point' => $request->input('rdv_point'),
                    'event_duration' => $request->input('event_duration'),
                    'audience_type' => $request->input('audience_type'),
                    'max_participants' => $request->integer('max_participants'),
                    'min_participants' => $request->integer('min_participants'),
                    'is_paid' => $request->boolean('is_paid'),
                    'price_details' => $request->input('price_details'),
                    'price_amount' => $request->boolean('is_paid') ? $request->float('price_amount') : null,
                    'image' => $imagePath,
                    'organizer_id' => Auth::id(),
                ]);

                if ($request->has('categories')) {
                    $event->categories()->sync($request->input('categories'));
                }

                $createdEvents[] = $event;
            }
        });

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Création événement',
            'details' => 'Création de '.count($createdEvents).' événement(s) : '.$request->input('title'),
            'ip_address' => request()->ip(),
        ]);

        // Supprimer le brouillon si présent
        if ($draftId = session('draft_id')) {
            EventDraft::where('id', $draftId)->where('organizer_id', Auth::id())->delete();
            session()->forget(['draft_data', 'draft_id']);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', count($createdEvents).' événement(s) créé(s) avec succès.');
    }

    public function modifier(int $id)
    {
        $event = Event::with('categories')->findOrFail($id);

        $this->authorize('manage', $event);

        $categories = Category::orderBy('name')->get();

        return view('admin.modifier-evenement', compact('event', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $event = Event::findOrFail($id);

        $this->authorize('manage', $event);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after_or_equal:today',
            'location' => 'nullable|string|max:200',
            'rdv_point' => 'nullable|string|max:200',
            'event_duration' => 'nullable|string|max:100',
            'audience_type' => 'nullable|string|max:100',
            'max_participants' => 'nullable|integer|min:1',
            'min_participants' => 'nullable|integer|min:0',
            'is_paid' => 'boolean',
            'price_details' => 'nullable|string',
            'price_amount' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'remove_image' => 'boolean',
        ]);

        // La capacité ne peut pas descendre sous les places déjà réservées.
        if ($request->filled('max_participants') && $request->integer('max_participants') < $event->reserved_places) {
            return back()->withErrors([
                'max_participants' => 'La capacité ne peut pas être inférieure aux places déjà réservées ('.$event->reserved_places.').',
            ])->withInput();
        }

        $data = $request->only([
            'title', 'description', 'event_date', 'location', 'rdv_point',
            'event_duration', 'audience_type', 'max_participants', 'min_participants',
            'price_details',
        ]);
        $data['is_paid'] = $request->boolean('is_paid');
        $data['price_amount'] = $request->boolean('is_paid') ? $request->float('price_amount') : null;

        // Gestion image
        if ($request->boolean('remove_image')) {
            if ($event->image) {
                @unlink(public_path('assets/images/animations/'.$event->image));
            }
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($event->image) {
                @unlink(public_path('assets/images/animations/'.$event->image));
            }
            $data['image'] = $this->uploadImage($request->file('image'));
        }

        $event->update($data);

        if ($request->has('categories')) {
            $event->categories()->sync($request->input('categories'));
        }

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Modification événement',
            'details' => 'Modification de l\'événement #'.$id.' : '.$request->input('title'),
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.evenements.modifier', $id)
            ->with('success', 'Événement modifié avec succès.');
    }

    public function supprimer(Request $request, int $id)
    {
        $event = Event::with('registrations')->findOrFail($id);

        $this->authorize('manage', $event);

        $title = $event->title;

        // Remboursement des participants ayant payé (avant la suppression).
        $refundedIds = [];
        foreach ($event->registrations as $registration) {
            if ($registration->payment_status === 'paid' && $registration->payment_intent_id) {
                try {
                    StripeService::refundPayment($registration->payment_intent_id);
                    $refundedIds[] = $registration->id;
                } catch (\Exception $e) {
                    Log::error('Erreur remboursement suppression événement: '.$e->getMessage());
                }
            }
        }

        // Notification de chaque participant avant la suppression :
        // le Mailable ne sérialise que des scalaires, l'envoi en file
        // d'attente reste valable une fois l'événement supprimé.
        foreach ($event->registrations as $registration) {
            MailService::sendEventDeletionNotification(
                $registration,
                $event,
                in_array($registration->id, $refundedIds),
            );
        }

        DB::transaction(function () use ($event) {
            if ($event->image) {
                @unlink(public_path('assets/images/animations/'.$event->image));
            }
            $event->categories()->detach();
            $event->registrations()->delete();
            $event->delete();
        });

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Suppression événement',
            'details' => 'Suppression de l\'événement #'.$id.' : '.$title
                .', '.count($event->registrations).' participant(s) notifié(s)',
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Événement supprimé avec succès. Les participants inscrits ont été notifiés.');
    }

    public function dupliquer(int $id)
    {
        $event = Event::with('categories')->findOrFail($id);

        $this->authorize('manage', $event);

        $newEvent = DB::transaction(function () use ($event) {
            $copy = Event::create([
                'title' => $event->title,
                'description' => $event->description,
                'event_date' => now(),
                'location' => $event->location,
                'rdv_point' => $event->rdv_point,
                'event_duration' => $event->event_duration,
                'audience_type' => $event->audience_type,
                'max_participants' => $event->max_participants,
                'min_participants' => $event->min_participants,
                'is_paid' => $event->is_paid,
                'price_details' => $event->price_details,
                'price_amount' => $event->price_amount,
                'organizer_id' => Auth::id(),
            ]);

            $copy->categories()->sync($event->categories->pluck('id'));

            return $copy;
        });

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Duplication événement',
            'details' => 'Duplication de l\'événement #'.$id.' vers #'.$newEvent->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.evenements.modifier', $newEvent->id)
            ->with('success', 'Événement dupliqué avec succès.');
    }

    public function saveDraft(Request $request)
    {
        $data = $request->except('_token');

        $draft = EventDraft::updateOrCreate(
            ['id' => $request->integer('draft_id', 0) ?: null],
            [
                'organizer_id' => Auth::id(),
                'draft_label' => $request->input('draft_label', 'Brouillon du '.now()->format('d/m/Y H:i')),
                'draft_data' => $data,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'draft_id' => $draft->id,
                'saved_at' => $draft->updated_at->format('H:i:s'),
            ]);
        }

        return redirect()->route('admin.evenements.creer', ['draft_id' => $draft->id])
            ->with('success', 'Brouillon sauvegardé.');
    }

    public function loadDraft(Request $request)
    {
        $draft = EventDraft::where('id', $request->integer('draft_id'))
            ->where('organizer_id', Auth::id())
            ->firstOrFail();

        session([
            'draft_data' => $draft->draft_data,
            'draft_id' => $draft->id,
        ]);

        $categories = Category::orderBy('name')->get();

        return view('admin.creer-evenement', compact('categories'))
            ->with('draftData', $draft->draft_data)
            ->with('draftId', $draft->id);
    }

    private function uploadImage($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'anim_'.bin2hex(random_bytes(16)).'.'.$extension;
        $file->move(public_path('assets/images/animations'), $filename);

        return $filename;
    }
}
