@extends('layouts.public')

@section('content')
<main class="container">
    <h1>Gérer mon inscription</h1>
    <h2>Animation : {{ $registration->event->title }}</h2>
    <p>📅 Le {{ $registration->event->event_date->format('d/m/Y à H\hi') }}</p>
    <p>👤 Inscription au nom de : {{ $registration->firstname . ' ' . $registration->lastname }}</p>

    @if ($event->is_paid && $event->price_amount && $registration->payment_status !== 'paid')
        <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
            <p style="margin: 0 0 5px 0;"><strong>⚠️ Paiement en attente</strong></p>
            <p style="margin: 0 0 10px 0;">
                Votre inscription est confirmée sous réserve de réception du règlement de
                <strong>{{ number_format((float)$event->price_amount * $registration->nb_participants, 2, ',', ' ') }} €</strong>.
            </p>
            <button data-action="open-window" data-url="{{ route('paiement', ['token' => $registration->token]) }}"
                style="display:inline-block;padding:10px 20px;background:#e67e22;color:white;border:none;border-radius:5px;cursor:pointer;font-size:0.95rem;">
                💳 Procéder au paiement
            </button>
        </div>
    @endif

    @if ($registration->payment_status === 'paid')
        <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; border-radius: 4px;">
            💳 <strong>Paiement effectué</strong> —
            Toute modification à la baisse ou annulation entraînera un remboursement automatique.
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="management-box" style="margin-top: 30px; display: flex; gap: 40px; flex-wrap: wrap;">

        <div class="card-manage" style="border: 1px solid #ccc; padding: 20px; flex: 1; min-width: 250px;">
            <h3>Modifier le nombre de places</h3>
            <form action="{{ route('reservation.gerer', ['token' => $registration->token]) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="update">
                <div class="form-group">
                    <label for="nb_participants">Nombre de personnes :</label>
                    <input type="number" name="nb_participants" id="nb_participants"
                        value="{{ $registration->nb_participants }}"
                        min="1" max="{{ $event->max_participants }}" required>
                </div>
                @if ($registration->payment_status === 'paid' && $event->is_paid && $event->price_amount)
                    <small style="display: block; margin-top: 5px; color: #856404;">
                        💳 Si vous augmentez le nombre de places, une nouvelle fenêtre s'ouvrira
                        pour le paiement du supplément (<strong>{{ number_format((float)$event->price_amount, 2, ',', ' ') }} €/pers.</strong>).
                    </small>
                @endif
                <button type="submit" class="btn-submit" style="margin-top: 10px;">Enregistrer les modifications</button>
            </form>
        </div>

        <div class="card-manage" style="border: 1px solid #f5c6cb; background: #fdf3f3; padding: 20px; flex: 1; min-width: 250px;">
            <h3 style="color: #721c24;">Se désister / Annuler</h3>
            <p>Vous avez un imprévu ? Libérez vos places pour permettre à d'autres citoyens de participer à cette animation.</p>
            <form action="{{ route('reservation.gerer', ['token' => $registration->token]) }}" method="POST"
                onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette inscription ?')">
                @csrf
                <input type="hidden" name="action" value="cancel">
                <button type="submit" class="btn-danger" style="background: #dc3545; color: white; padding: 10px; border: none; cursor: pointer;">
                    Annuler mon inscription définitivement ❌
                </button>
            </form>
        </div>

    </div>
</main>

<script src="{{ asset('assets/_js/admin-utils.js') }}" defer></script>
@endsection
