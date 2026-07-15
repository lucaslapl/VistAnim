@extends('layouts.public')

@section('content')
<main class="container" style="font-family: sans-serif;">
    @if ($registration->event->is_paid && $registration->event->price_amount)
        @if ($registration->payment_status === 'paid')
            <div class="alert alert-success">
                <h1>✅ Inscription et paiement confirmés !</h1>
                <p>Merci pour votre règlement de
                    <strong>{{ number_format((float)$registration->event->price_amount * $registration->nb_participants, 2, ',', ' ') }} €</strong>.
                </p>
                <p>Un email de confirmation vient de vous être envoyé.</p>
            </div>
        @else
            <div class="alert alert-success" id="confirmation-box">
                <h1>🎉 Inscription validée !</h1>
                <p>Merci pour votre engagement environnemental.</p>

                <div id="payment-pending">
                    <p>💳 <strong>Animation payante</strong> — Veuillez procéder au paiement sécurisé.</p>
                    <p>Montant : <strong id="total-price">
                            {{ number_format((float)$registration->event->price_amount * $registration->nb_participants, 2, ',', ' ') }} €
                        </strong></p>
                    <p style="margin-top: 15px;">
                        <button data-action="open-payment" data-token="{{ $registration->token }}" data-auto-open="1"
                            data-url="{{ route('paiement', ['token' => $registration->token]) }}"
                            data-verify-url="{{ route('paiement.verifier', ['token' => $registration->token]) }}" class="btn-primary"
                            style="display: inline-block; padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 1.1rem; cursor: pointer;">
                            💳 Payer maintenant
                        </button>
                    </p>
                    <p><small>Le paiement s'ouvre dans un nouvel onglet. Revenez ici après le paiement.</small></p>
                </div>

                <div id="payment-completed" style="display: none;">
                    <h2 style="color: #155724;">✅ Paiement confirmé !</h2>
                    <p>Votre inscription et votre règlement ont bien été pris en compte.</p>
                </div>

                <div id="payment-failed" style="display: none;">
                    <h2 style="color: #856404;">⏳ Paiement en attente</h2>
                    <p>Si vous avez fermé la fenêtre sans payer, vous pouvez réessayer.</p>
                </div>
            </div>

            <script src="{{ asset('assets/_js/payment.js') }}" defer></script>
        @endif
    @else
        <div class="alert alert-success">
            <h1>🎉 Inscription validée !</h1>
            <p>Merci pour votre engagement environnemental.</p>
            <p>Un email de confirmation vient de vous être envoyé.</p>
        </div>
    @endif

    <div style="margin-top: 20px;">
        <a href="{{ route('reservation.gerer', ['token' => $registration->token]) }}">Voir / gérer ma réservation</a><br>
        <a href="{{ route('accueil') }}">⬅️ Retour à l'accueil</a>
    </div>
</main>
@endsection
