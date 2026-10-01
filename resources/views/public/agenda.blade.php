@extends('layouts.public')

@section('content')
<main class="container">
    <section class="hero">
        <h1>Animations de l'ABC de DémoVille</h1>
        <p>Découvrez et inscrivez-vous aux animations et sorties nature de votre ville !</p>
    </section>

    <h2 class="section-title">Prochaines animations</h2>

    @if ($events->isEmpty())
        <p class="no-events">Aucune animation n'est prévue pour le moment. Revenez bientôt ! 🌿</p>
    @else
        <div class="events-grid">
            @foreach ($events as $event)
                <article class="event-card">
                    <div class="event-card-header">
                        <div class="animation-meta" style="margin-bottom: 15px;">
                            @if ($event->categories->isNotEmpty())
                                <div class="categories-container" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                                    @foreach ($event->categories as $category)
                                        <span class="badge" style="background-color: #e3f2fd; color: #0d47a1; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; border: 1px solid #bbdefb;">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span style="font-style: italic; color: #999; font-size: 0.85rem;">Aucune catégorie</span>
                            @endif
                        </div>
                        <span class="association-name">Par : {{ $event->organizer->name }}</span>
                    </div>

                    <div class="event-card-body">
                        <h3>{{ $event->title }}</h3>
                        <p class="event-meta">
                            📅 <strong>Date :</strong> {{ $event->event_date->format('d/m/Y à H\hi') }}<br>
                            📍 <strong>Lieu :</strong> {{ $event->location }}<br>
                        </p>
                        <div class="event-card-image">
                            <img src="{{ asset('assets/images/animations/' . ($event->image ?: 'placeholder.svg')) }}"
                                alt="{{ $event->title }}">
                        </div>
                    </div>

                    <div class="event-card-footer">
                        <a href="{{ route('animation.detail', $event->id) }}" class="btn-primary">En savoir plus / S'inscrire</a>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $events->links() }}
    @endif
</main>
@endsection
