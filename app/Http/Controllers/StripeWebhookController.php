<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('stripe.webhook_secret')
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'checkout.session.expired' => $this->handleCheckoutExpired($event->data->object),
            'charge.refunded' => $this->handleRefund($event->data->object),
            default => Log::info('Stripe webhook non géré: '.$event->type),
        };

        return response()->json(['received' => true]);
    }

    private function handleCheckoutCompleted($session)
    {
        $query = Registration::query();

        if (! empty($session->payment_intent)) {
            $query->where('payment_intent_id', $session->payment_intent);
        }
        if (! empty($session->metadata->registration_id)) {
            $query->orWhere('id', $session->metadata->registration_id);
        }

        $registration = $query->first();

        if ($registration && $session->payment_status === 'paid') {
            $registration->update([
                'payment_status' => 'paid',
                'payment_intent_id' => $session->payment_intent,
            ]);
        }
    }

    private function handleCheckoutExpired($session)
    {
        $registration = null;

        if (! empty($session->payment_intent)) {
            $registration = Registration::where('payment_intent_id', $session->payment_intent)->first();
        }

        $registration ??= ! empty($session->metadata->registration_id)
            ? Registration::find($session->metadata->registration_id)
            : null;

        // Une inscription non payée ne doit pas bloquer les places indéfiniment.
        if ($registration && $registration->payment_status === 'pending') {
            $registration->update(['payment_status' => 'expired']);
        }
    }

    private function handleRefund($charge)
    {
        $registration = Registration::where('payment_intent_id', $charge->payment_intent)->first();

        if (! $registration) {
            return;
        }

        $amount = $charge->amount_captured ?? $charge->amount ?? 0;
        $refunded = $charge->amount_refunded ?? 0;

        // Un remboursement partiel (réduction du nombre de places) ne change
        // pas le statut : seul un remboursement total marque 'refunded'.
        if ($amount > 0 && $refunded >= $amount) {
            $registration->update(['payment_status' => 'refunded']);
        }
    }
}
