<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Services\MailService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicRegistrationController extends Controller
{
    public function afficherFormulaire(int $id)
    {
        $event = Event::with('organizer', 'categories')->findOrFail($id);

        if ($event->remaining_places !== null && $event->remaining_places <= 0) {
            return redirect()->route('agenda')->with('error', 'Cet événement est complet.');
        }

        // Génération CAPTCHA si absent
        if (!session()->has('captcha_result')) {
            $this->generateCaptcha();
        }

        return view('public.formulaire-inscription', compact('event'));
    }

    public function traiterInscription(Request $request, int $id)
    {
        if (!$request->isMethod('post')) {
            abort(405);
        }

        $event = Event::findOrFail($id);

        // Validation
        $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'nb_participants' => 'required|integer|min:1',
            'consent' => 'accepted',
        ]);

        // Honeypot
        if ($request->filled('website')) {
            return redirect()->route('accueil');
        }

        // CAPTCHA
        if ((int) $request->input('captcha') !== session('captcha_result')) {
            $this->generateCaptcha();
            return back()->withInput()->withErrors(['captcha' => 'Captcha incorrect.']);
        }
        session()->forget(['captcha_result', 'captcha_num1', 'captcha_num2']);

        // IP limiting
        $ipKey = 'inscription_ip:' . $id . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 3)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return back()->withErrors([
                'email' => 'Trop d\'inscriptions depuis cette IP. Réessayez dans ' . ceil($seconds / 60) . ' minutes.',
            ])->withInput();
        }

        // Vérification doublon email
        $existing = Registration::where('event_id', $id)
            ->where('email', $request->input('email'))
            ->exists();

        if ($existing) {
            return redirect()->route('ticket.recuperer', ['event_id' => $id])
                ->with('info', 'Vous êtes déjà inscrit à cet événement.');
        }

        $registration = null;

        DB::transaction(function () use ($request, $event, $id, &$registration) {
            // Verrouillage ligne
            Event::where('id', $id)->lockForUpdate()->first();

            // Vérification places restantes
            $reserved = $event->fresh()->reserved_places;
            $requestedPlaces = $request->integer('nb_participants');

            if ($event->max_participants !== null && ($reserved + $requestedPlaces) > $event->max_participants) {
                throw ValidationException::withMessages([
                    'nb_participants' => 'Il n\'y a plus assez de places disponibles.',
                ]);
            }

            $registration = Registration::create([
                'event_id' => $id,
                'firstname' => $request->input('firstname'),
                'lastname' => $request->input('lastname'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'nb_participants' => $requestedPlaces,
                'token' => Str::random(64),
                'consent' => true,
                'user_ip' => $request->ip(),
                'payment_status' => $event->is_paid ? 'pending' : null,
            ]);
        });

        RateLimiter::hit($ipKey, 3600);

        MailService::sendRegistrationConfirmation($registration, $event);
        MailService::sendAdminNotification($registration, $event);

        if ($event->is_paid) {
            return redirect()->route('paiement', ['token' => $registration->token]);
        }

        return redirect()->route('confirmation', ['token' => $registration->token]);
    }

    public function confirmation(string $token)
    {
        $registration = Registration::where('token', $token)
            ->with('event.organizer')
            ->firstOrFail();

        return view('public.confirmation', compact('registration'));
    }

    public function afficherRecuperationTicket(Request $request)
    {
        if (!session()->has('captcha_result')) {
            $this->generateCaptcha();
        }

        return view('public.retrouver-ticket');
    }

    public function traiterRecuperationTicket(Request $request)
    {
        if ((int) $request->input('captcha') !== session('captcha_result')) {
            $this->generateCaptcha();
            return back()->withErrors(['captcha' => 'Captcha incorrect.'])->withInput();
        }

        session()->forget(['captcha_result', 'captcha_num1', 'captcha_num2']);

        $eventId = $request->input('event_id');
        $email = $request->input('email');

        $registration = Registration::where('event_id', $eventId)
            ->where('email', $email)
            ->first();

        if ($registration) {
            MailService::sendTicketRecovery($registration, $registration->event);
        }

        return redirect()->route('reservation.gerer', [
            'token' => $registration?->token ?? 'invalide',
        ])->with('success', 'Si l\'email correspond à une inscription, vous allez recevoir un message.');
    }

    public function gererReservation(Request $request, string $token)
    {
        $registration = Registration::where('token', $token)
            ->with('event.organizer')
            ->first();

        if (!$registration) {
            return redirect()->route('accueil');
        }

        $event = $registration->event;

        // Retour Stripe après ajout de places
        if ($request->has('add') && $request->has('session_id')) {
            return $this->handleStripeModificationReturn($request, $registration, $event);
        }

        // Actions POST
        if ($request->isMethod('post')) {
            return $this->traiterModificationReservation($request, $registration, $event);
        }

        return view('public.gestion-reservation', compact('registration', 'event'));
    }

    private function traiterModificationReservation(Request $request, Registration $registration, Event $event)
    {
        $action = $request->input('action');

        if ($action === 'cancel') {
            $refunded = false;

            if ($registration->payment_status === 'paid' && $registration->payment_intent_id) {
                try {
                    StripeService::refundPayment($registration->payment_intent_id);
                    $refunded = true;
                } catch (\Exception $e) {
                    Log::error('Erreur remboursement annulation: ' . $e->getMessage());
                }
            }

            $registration->delete();

            MailService::sendCancellationConfirmation($registration, $event, $refunded);
            MailService::sendAdminCancellationNotification($registration, $event, $refunded);

            return view('public.annulation-succes', compact('registration', 'event'));
        }

        if ($action === 'update') {
            $request->validate(['nb_participants' => 'required|integer|min:1']);

            $oldNb = $registration->nb_participants;
            $newNb = $request->integer('nb_participants');
            $diff = $newNb - $oldNb;

            return DB::transaction(function () use ($request, $registration, $event, $oldNb, $newNb, $diff) {
                Event::where('id', $event->id)->lockForUpdate()->first();

                if ($event->max_participants !== null) {
                    $reserved = $event->fresh()->reserved_places;
                    if (($reserved + $diff) > $event->max_participants) {
                        return back()->withErrors(['nb_participants' => 'Pas assez de places disponibles.']);
                    }
                }

                if ($diff > 0 && $event->is_paid) {
                    // Paiement supplémentaire
                    $successUrl = route('reservation.gerer', [
                        'token' => $registration->token,
                        'add' => '1',
                        'session_id' => '{CHECKOUT_SESSION_ID}',
                    ]);
                    $cancelUrl = route('reservation.gerer', [
                        'token' => $registration->token,
                        'cancel' => '1',
                    ]);

                    $session = StripeService::createCheckoutSession(
                        $event,
                        $registration,
                        $successUrl,
                        $cancelUrl,
                        ['new_nb' => (string) $newNb],
                    );

                    return redirect()->away($session->url);
                }

                $refundInfo = null;

                if ($diff < 0 && $registration->payment_status === 'paid') {
                    $refundAmount = StripeService::calculateRefundAmount(
                        $event->price_amount, $oldNb, $newNb
                    );
                    if ($refundAmount > 0) {
                        StripeService::refundPayment($registration->payment_intent_id, $refundAmount);
                        $refundedAmount = number_format($refundAmount / 100, 2, ',', ' ') . ' €';
                        $refundInfo = "Remboursement de {$refundedAmount} initié (sous 5 à 10 jours ouvrés).";
                    }
                }

                $registration->update(['nb_participants' => $newNb]);

                MailService::sendModificationConfirmation($registration, $event, $oldNb, $refundInfo);

                return redirect()->route('reservation.gerer', ['token' => $registration->token])
                    ->with('success', 'Réservation mise à jour.');
            });
        }

        return back();
    }

    private function handleStripeModificationReturn(Request $request, Registration $registration, Event $event)
    {
        try {
            $session = StripeService::getSession($request->query('session_id'));

            // Le nouveau nombre de places vient des métadonnées Stripe (créées
            // côté serveur), jamais de l'URL : empêche la falsification.
            $newNb = (int) ($session->metadata->new_nb ?? 0);

            if ($session->payment_status === 'paid' && $newNb > 0) {
                $registration->update([
                    'nb_participants' => $newNb,
                    'payment_status' => 'paid',
                ]);
                return redirect()->route('reservation.gerer', ['token' => $registration->token])
                    ->with('success', 'Réservation mise à jour avec les places supplémentaires.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur retour Stripe modification: ' . $e->getMessage());
        }

        return redirect()->route('reservation.gerer', ['token' => $registration->token])
            ->with('error', 'Erreur lors du paiement supplémentaire.');
    }

    public function getClientIp(Request $request): string
    {
        return $request->header('HTTP_CLIENT_IP')
            ?? $request->header('HTTP_X_FORWARDED_FOR')
            ?? $request->ip();
    }

    private function generateCaptcha(): void
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        session([
            'captcha_result' => $num1 + $num2,
            'captcha_num1' => $num1,
            'captcha_num2' => $num2,
        ]);
    }
}