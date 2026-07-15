@extends('layouts.public')

@section('content')
<main class="container">
    <a href="{{ route('animation.detail', $event->id) }}" class="btn-back">⬅️ Retour aux détails de l'animation</a>

    <section class="registration-form-page">
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <h1>Inscription</h1>
        <h2>Animation : {{ $event->title }}</h2>
        <p class="event-reminder">
            📅 Le {{ $event->event_date->format('d/m/Y à H\hi') }}<br>
            📍 Lieu : {{ $event->location }}
        </p>

        @if ($event->is_paid && $event->price_amount)
            <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>💰 Animation payante</strong>
                <p style="margin: 5px 0 0 0;">
                    Prix : <strong>{{ number_format((float)$event->price_amount, 2, ',', ' ') }} € par personne</strong>
                </p>
                @if ($event->price_details)
                    <small>{!! nl2br(e($event->price_details)) !!}</small>
                @endif
                <p style="margin: 8px 0 0 0; font-size: 0.9rem;">
                    💳 Le paiement s'effectuera après validation de votre inscription.
                </p>
            </div>
        @endif

        <form action="{{ route('inscription.traiter', $event->id) }}" method="POST" class="form-card">
            @csrf

            <div style="display:none;">
                <label for="website">Ne pas remplir :</label>
                <input type="text" name="website" id="website" value="">
            </div>

            <div class="form-group">
                <label for="firstname">Prénom :</label>
                <input type="text" name="firstname" id="firstname" value="{{ old('firstname') }}" required>
            </div>

            <div class="form-group">
                <label for="lastname">Nom :</label>
                <input type="text" name="lastname" id="lastname" value="{{ old('lastname') }}" required>
            </div>

            <div class="form-group">
                <label for="email">Adresse Email :</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="phone">Numéro de téléphone :</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required>
            </div>

            <div class="form-group">
                <label for="nb_participants">Nombre de places à réserver :</label>
                <input type="number" name="nb_participants" id="nb_participants" value="{{ old('nb_participants', 1) }}"
                    min="1" max="{{ $event->remaining_places }}" required
                    data-price="{{ $event->price_amount }}">
                <small class="form-help">
                    (Il reste {{ $event->remaining_places }} places disponibles)
                    @if ($event->is_paid && $event->price_amount)
                        — <span id="price-total">
                            Total : <strong>{{ number_format((float)$event->price_amount, 2, ',', ' ') }} €</strong>
                        </span>
                    @endif
                </small>
            </div>

            <div class="form-group" style="margin-bottom: 15px; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #e3e6f0;">
                <label for="captcha" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    🛡️ Sécurité : Combien font {{ session('captcha_num1') }} + {{ session('captcha_num2') }} ?
                </label>
                <input type="number" name="captcha" id="captcha" required style="width: 80px; padding: 6px;" placeholder="?">
            </div>

            <div class="form-group consent-group">
                <label class="consent-label">
                    <input type="checkbox" name="consent" value="1" required>
                    <span>J'accepte la <a href="{{ route('politique-confidentialite') }}" target="_blank">politique de confidentialité</a></span>
                </label>
            </div>

            <button type="submit" class="btn-submit">Confirmer mon inscription 🌿</button>
        </form>
    </section>
    @if ($event->is_paid && $event->price_amount)
        <script src="{{ asset('assets/_js/price-calculator.js') }}" defer></script>
    @endif
</main>
@endsection
