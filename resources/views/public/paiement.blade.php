@extends('layouts.public')

@section('content')
<main class="container" style="font-family: sans-serif; text-align: center; padding: 40px 20px;">
    @if ($status === 'success')
        <div class="alert alert-success" style="max-width: 500px; margin: 40px auto; text-align: center;">
            <h1>✅ Paiement confirmé !</h1>
            <p>Votre règlement de
                <strong>{{ number_format((float)$event->price_amount * (int)$registration->nb_participants, 2, ',', ' ') }} €</strong>
                a bien été reçu.
            </p>
            <p style="margin-top: 20px;">✅ Vous pouvez fermer cette fenêtre.</p>
            <p style="margin-top: 10px;">
                <button data-action="close-window" class="btn-primary" style="padding: 10px 20px; cursor: pointer;">
                    Fermer cette fenêtre
                </button>
            </p>
        </div>
        <div data-auto-close="3000"></div>
        <script src="{{ asset('assets/_js/payment.js') }}" defer></script>

    @elseif ($status === 'cancel')
        <div class="alert alert-warning" style="max-width: 500px; margin: 0 auto;">
            <h1>⏸️ Paiement annulé</h1>
            <p>Vous n'avez pas encore payé. Vous pouvez réessayer quand vous voulez.</p>
            <p style="margin-top: 20px;">
                <a href="{{ route('paiement', ['token' => $registration->token]) }}" class="btn-primary">
                    🔄 Réessayer le paiement
                </a>
                <br><br>
                <a href="{{ route('reservation.gerer', ['token' => $registration->token]) }}" style="color: #666;">
                    Gérer ma réservation
                </a>
            </p>
        </div>

    @elseif ($status === 'error')
        <div class="alert alert-danger" style="max-width: 500px; margin: 0 auto;">
            <h1>❌ Erreur de paiement</h1>
            <p>{{ htmlspecialchars($error ?? 'Une erreur technique est survenue. Contactez l\'organisateur.') }}</p>
            <p style="margin-top: 20px;">
                <a href="{{ route('paiement', ['token' => $registration->token]) }}" class="btn-primary">
                    🔄 Réessayer le paiement
                </a>
                <br><br>
                <a href="{{ route('reservation.gerer', ['token' => $registration->token]) }}" style="color: #666;">
                    Gérer ma réservation
                </a>
            </p>
        </div>

    @else
        <div class="alert alert-info" style="max-width: 500px; margin: 0 auto;">
            <h1>⏳ Redirection vers le paiement...</h1>
            <p>Montant à payer :
                <strong>{{ number_format((float)$event->price_amount * (int)$registration->nb_participants, 2, ',', ' ') }} €</strong>
            </p>
            <p>pour l'animation <strong>{{ $event->title }}</strong>
                ({{ (int)$registration->nb_participants }} place(s) à
                {{ number_format((float)$event->price_amount, 2, ',', ' ') }} €/pers.)</p>
            <p style="margin-top: 20px;">
                <a href="{{ route('paiement', ['token' => $registration->token]) }}" class="btn-primary">
                    💳 Procéder au paiement
                </a>
            </p>
        </div>
    @endif

    <p style="margin-top: 30px;">
        <a href="{{ route('accueil') }}">⬅️ Retour à l'agenda</a>
    </p>
</main>
@endsection
