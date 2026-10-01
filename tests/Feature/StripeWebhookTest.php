<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['stripe.webhook_secret' => 'whsec_test_secret']);
    }

    private function postWebhook(array $payload): TestResponse
    {
        $json = json_encode($payload);
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$json}", config('stripe.webhook_secret'));

        return $this->call('POST', 'stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
            'CONTENT_TYPE' => 'application/json',
        ], $json);
    }

    private function eventPayload(string $type, array $object): array
    {
        return [
            'id' => 'evt_test_'.uniqid(),
            'object' => 'event',
            'type' => $type,
            'data' => ['object' => $object],
        ];
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $payload = json_encode($this->eventPayload('checkout.session.completed', ['payment_status' => 'paid']));

        $this->call('POST', 'stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't=1,v1=invalid',
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(400);
    }

    public function test_checkout_completed_marks_registration_paid(): void
    {
        $registration = Registration::factory()->pending()->create();

        $this->postWebhook($this->eventPayload('checkout.session.completed', [
            'payment_intent' => 'pi_test_123',
            'payment_status' => 'paid',
            'metadata' => ['registration_id' => (string) $registration->id],
        ]))->assertOk();

        $registration = $registration->fresh();
        $this->assertSame('paid', $registration->payment_status);
        $this->assertSame('pi_test_123', $registration->payment_intent_id);
    }

    public function test_checkout_expired_frees_pending_registration_places(): void
    {
        // Régression P0-4 : une session de paiement expirée ne doit pas
        // bloquer les places indéfiniment.
        $event = Event::factory()->limited(2)->create();
        $registration = Registration::factory()->pending()->for($event)->create(['nb_participants' => 2]);

        $this->assertSame(0, $event->fresh()->remaining_places);

        $this->postWebhook($this->eventPayload('checkout.session.expired', [
            'payment_intent' => null,
            'client_reference_id' => (string) $registration->id,
            'metadata' => ['registration_id' => (string) $registration->id],
        ]))->assertOk();

        $this->assertSame('expired', $registration->fresh()->payment_status);
        $this->assertSame(2, $event->fresh()->remaining_places);
        $this->get(route('inscription.form', $event->id))->assertOk();
    }

    public function test_partial_refund_keeps_registration_paid(): void
    {
        // Régression P0-3 : un remboursement partiel (réduction de places)
        // ne doit pas marquer toute la réservation comme remboursée.
        $registration = Registration::factory()->paid()->create();

        $this->postWebhook($this->eventPayload('charge.refunded', [
            'payment_intent' => $registration->payment_intent_id,
            'amount' => 3000,
            'amount_captured' => 3000,
            'amount_refunded' => 1000,
        ]))->assertOk();

        $this->assertSame('paid', $registration->fresh()->payment_status);
    }

    public function test_full_refund_marks_registration_refunded(): void
    {
        $registration = Registration::factory()->paid()->create();

        $this->postWebhook($this->eventPayload('charge.refunded', [
            'payment_intent' => $registration->payment_intent_id,
            'amount' => 3000,
            'amount_captured' => 3000,
            'amount_refunded' => 3000,
        ]))->assertOk();

        $this->assertSame('refunded', $registration->fresh()->payment_status);
    }

    public function test_unhandled_event_type_is_acknowledged(): void
    {
        $this->postWebhook($this->eventPayload('payment_intent.created', ['id' => 'pi_x']))
            ->assertOk()
            ->assertJson(['received' => true]);
    }
}
