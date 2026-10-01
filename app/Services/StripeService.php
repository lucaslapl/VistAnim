<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\StripeClient;

class StripeService
{
    private static ?StripeClient $client = null;

    private static function client(): StripeClient
    {
        if (self::$client === null) {
            self::$client = new StripeClient(config('stripe.secret_key'));
        }
        return self::$client;
    }

    public static function createCheckoutSession(Event $event, Registration $reg, string $successUrl, string $cancelUrl, array $extraMetadata = []): Session
    {
        $unitAmount = (int)(round((float)$event->price_amount, 2) * 100);
        $quantity = (int)$reg->nb_participants;

        $session = self::client()->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $reg->email,
            'client_reference_id' => (string)$reg->id,
            'metadata' => array_merge([
                'registration_id' => (string)$reg->id,
                'event_id' => (string)$event->id,
                'token' => $reg->token,
            ], $extraMetadata),
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $event->title,
                        'description' => 'Inscription pour ' . $quantity . ' personne(s)',
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => $quantity,
            ]],
        ]);

        return $session;
    }

    public static function getSession(string $sessionId): Session
    {
        return self::client()->checkout->sessions->retrieve($sessionId);
    }

    public static function getPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return self::client()->paymentIntents->retrieve($paymentIntentId);
    }

    public static function refundPayment(string $paymentIntentId, ?int $amountCents = null): Refund
    {
        $params = ['payment_intent' => $paymentIntentId];
        if ($amountCents !== null) {
            $params['amount'] = $amountCents;
        }
        return self::client()->refunds->create($params);
    }

    public static function calculateRefundAmount(float $pricePerPerson, int $oldNb, int $newNb): int
    {
        $diff = $oldNb - $newNb;
        return (int)(round($diff * $pricePerPerson, 2) * 100);
    }
}
