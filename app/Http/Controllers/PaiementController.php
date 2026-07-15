<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Services\MailService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaiementController extends Controller
{
    public function handle(Request $request)
    {
        $registration = Registration::where('token', $request->query('token'))->first();
        $event = $registration?->event;

        if (!$registration || !$event) {
            return redirect()->route('accueil');
        }

        // Déjà payé ou gratuit → confirmation
        if ($registration->payment_status === 'paid' || !$event->is_paid) {
            return redirect()->route('confirmation', ['token' => $registration->token]);
        }

        // Retour Stripe
        if ($request->has('success') || $request->has('cancel')) {
            return $this->handleStripeReturn($request, $registration, $event);
        }

        // Redirection vers Stripe
        return $this->redirectToStripe($event, $registration);
    }

    public function verifier(Request $request)
    {
        $registration = Registration::where('token', $request->query('token'))->first();

        if (!$registration) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json(['status' => $registration->payment_status]);
    }

    private function handleStripeReturn(Request $request, Registration $registration, Event $event)
    {
        $status = 'error';
        $error = null;

        if ($request->has('success') && $request->has('session_id')) {
            try {
                $session = StripeService::getSession($request->query('session_id'));

                if ($session->payment_status === 'paid') {
                    $registration->update([
                        'payment_status' => 'paid',
                        'payment_intent_id' => $session->payment_intent,
                    ]);

                    MailService::sendPaymentConfirmation($registration, $event);
                    $status = 'success';
                } else {
                    $status = 'cancel';
                }
            } catch (\Exception $e) {
                Log::error('Erreur vérification paiement Stripe: ' . $e->getMessage());
                $error = 'Erreur de vérification du paiement.';
            }
        } elseif ($request->has('cancel')) {
            $status = 'cancel';
        }

        return view('public.paiement', compact('status', 'error', 'registration', 'event'));
    }

    private function redirectToStripe(Event $event, Registration $registration)
    {
        $successUrl = route('paiement', [
            'token' => $registration->token,
            'success' => '1',
            'session_id' => '{CHECKOUT_SESSION_ID}',
        ]);
        $cancelUrl = route('paiement', [
            'token' => $registration->token,
            'cancel' => '1',
        ]);

        try {
            $session = StripeService::createCheckoutSession(
                $event,
                $registration,
                $successUrl,
                $cancelUrl
            );

            return redirect()->away($session->url);
        } catch (\Exception $e) {
            Log::error('Erreur création session Stripe: ' . $e->getMessage());

            return view('public.paiement', [
                'status' => 'error',
                'error' => 'Erreur de connexion au paiement. Veuillez réessayer.',
                'registration' => $registration,
                'event' => $event,
            ]);
        }
    }
}