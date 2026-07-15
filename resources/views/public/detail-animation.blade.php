@extends('layouts.public')

@section('content')
<main class="container">
    <a href="{{ route('accueil') }}" class="btn-back">⬅️ Retour à l'agenda</a>

    <article class="event-detail">
        <h1>{{ $event->title }}</h1>
        <p class="organizer">Organisé par : <strong>{{ $event->organizer->name }}</strong></p>

        <div class="animation-meta" style="margin-bottom: 20px;">
            @if ($event->categories->isNotEmpty())
                <div class="categories-container" style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px;">
                    <span style="font-weight: bold; color: #555;">📁 Catégorie(s) :</span>
                    @foreach ($event->categories as $category)
                        <span class="badge" style="background-color: #e3f2fd; color: #0d47a1; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; border: 1px solid #bbdefb;">
                            {{ $category->name }}
                        </span>
                    @endforeach
                </div>
            @else
                <p style="font-style: italic; color: #888;">Aucune catégorie spécifiée pour cette animation.</p>
            @endif
        </div>

        <div class="event-infos">
            <p>📅 <strong>Date et heure :</strong> {{ $event->event_date->format('d/m/Y à H\hi') }}</p>
            <p>📍 <strong>Lieu de rendez-vous :</strong> {{ $event->location }}</p>
            <p>👥 <strong>Jauge :</strong> {{ $event->reserved_places }} / {{ $event->max_participants }} participants (Il reste {{ $event->remaining_places }} places)</p>

            @if ($event->is_paid)
                <div class="panel panel-warning">
                    💰 <strong>Animation payante :</strong>
                    <p>{!! nl2br(e($event->price_details)) !!}</p>
                    @if ($event->price_amount)
                        <p style="font-size: 1.2rem; font-weight: bold; margin-top: 5px;">
                            {{ number_format((float)$event->price_amount, 2, ',', ' ') }} € / personne
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="event-detail-image">
            <img src="{{ asset('assets/images/animations/' . ($event->image ?: 'placeholder.svg')) }}"
                alt="{{ $event->title }}"
                style="max-width: 100%; border-radius: 8px; margin-bottom: 20px;">
        </div>

        <div class="event-description-full">
            <h3>Description de l'activité</h3>
            <p>{!! nl2br(e($event->description)) !!}</p>
        </div>

        <hr>

        @if ($event->min_participants > 0)
            <div class="status-box" style="padding: 15px; border-radius: 5px; margin-bottom: 20px; background: #f8f9fa; border: 1px solid #ddd;">
                <h3>📊 Statut de l'activité :</h3>
                @if ($event->reserved_places >= $event->min_participants)
                    <p style="color: #155724; font-weight: bold; margin: 0;">✅ Animation confirmée ! (Seuil minimum atteint).</p>
                @else
                    <p style="color: #856404; font-weight: bold; margin-bottom: 5px;">⏳ En attente d'inscriptions.</p>
                    <p style="font-size: 0.9rem; color: #555; margin: 0;">
                        Actuellement : <strong>{{ $event->reserved_places }}</strong> inscrit(s).
                        Cette animation nécessite un minimum de <strong>{{ $event->min_participants }}</strong> inscription(s) pour qu'elle soit confirmée.
                    </p>
                @endif
            </div>
        @endif

        <section class="registration-link-section">
            @if ($event->remaining_places !== null && $event->remaining_places <= 0)
                <p class="alert alert-danger">❌ Désolé, cette animation est complète !</p>
            @else
                <p>Il reste <strong>{{ $event->remaining_places }} places disponibles</strong> pour cette sortie.</p>
                <a href="{{ route('inscription.form', $event->id) }}" class="btn-primary btn-large">
                    S'inscrire à cette animation 🌿
                </a>
            @endif
            <div class="already-registered-box" style="margin-top: 20px; text-align: center;">
                <p style="font-size: 0.95rem; color: #666;">
                    Vous êtes déjà inscrit à cette animation et vous souhaitez modifier ou annuler vos places ?
                </p>
                <a href="{{ route('ticket.recuperer', ['event_id' => $event->id]) }}" class="btn-secondary" style="color: #0056b3; text-decoration: underline;">
                    👉 Cliquez ici pour modifier votre réservation
                </a>
            </div>
        </section>
    </article>
</main>
@endsection
