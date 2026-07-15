<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:60,1');
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('stripe.webhook_secret')
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'charge.refunded' => $this->handleRefund($event->data->object),
            default => Log::info('Stripe webhook non géré: ' . $event->type),
        };

        return response()->json(['received' => true]);
    }

    private function handleCheckoutCompleted($session)
    {
        $registration = Registration::where('payment_intent_id', $session->payment_intent)
            ->orWhere('id', $session->metadata->registration_id ?? null)
            ->first();

        if ($registration && $session->payment_status === 'paid') {
            $registration->update([
                'payment_status' => 'paid',
                'payment_intent_id' => $session->payment_intent,
            ]);
        }
    }

    private function handleRefund($charge)
    {
        $registration = Registration::where('payment_intent_id', $charge->payment_intent)->first();

        if ($registration) {
            $registration->update(['payment_status' => 'refunded']);
        }
    }
}