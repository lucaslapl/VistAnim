@extends('layouts.public')

@section('content')
<main class="container" style="margin-top: 40px;">
    <div id="main-content" class="flex gap-40 flex-wrap space-between">
        <div id="intro">
            <h1>Découvrez la nature près de chez vous !</h1>
            <p class="intro-welcome">
                Bienvenue sur la plateforme de l'ABC de <strong>{{ config('org.name') }}</strong>.
                Notre mission est de répertorier, protéger et valoriser la faune et la flore de notre territoire.
            </p>
            <p>
                Tout au long de l'année, la municipalité et les associations naturalistes locales vous proposent
                des sorties gratuites ou participatives : initiations à l'ornithologie, chasses aux insectes,
                découverte des plantes sauvages ou chantiers participatifs.
            </p>
            <div style="margin-top: 30px;">
                <a href="{{ route('agenda') }}" class="btn-primary">
                    Voir l'agenda complet
                </a>
            </div>
        </div>

        <div id="next-events">
            <h2>📅 Prochaines animations ({{ $stats['coming_count'] }})</h2>

            @if ($events->isEmpty())
                <p style="font-style: italic; color: #666;">Aucune animation de prévue pour le moment. Repassez bientôt ! 🌿</p>
            @else
                <div class="flex flex-column gap-15" style="margin-top: 15px;">
                    @foreach ($events as $event)
                        <a href="{{ route('animation.detail', $event->id) }}" class="event-card">
                            <span>{{ $event->event_date->format('d/m/Y à H\hi') }}</span>
                            <h3>{{ $event->title }}</h3>
                            <p>📍 {{ $event->location }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</main>

<section id="anim-stats">
    <div class="container flex space-between align-center flex-wrap gap-30">
        <div class="stat-item">
            <span>{{ $stats['past_events'] }}</span>
            <p>Animations organisées</p>
        </div>
        <div class="stat-item">
            <span>{{ $stats['past_participants'] }}</span>
            <p>Citoyens participants</p>
        </div>
        <div class="stat-item">
            <span>{{ $stats['structures'] }}</span>
            <p>Structures & Assos actives</p>
        </div>
    </div>
</section>
@endsection
