<header class="main-header">
    <span class="tool-name">{{ config('app.name') }}</span>
    <div class="nav-container">
        <a href="{{ route('accueil') }}" class="logo-link">
            {!! config('org.logo_text') !!}
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Menu">☰</button>
        <nav class="nav-menu" id="navMenu">
            <a href="{{ route('accueil') }}">Accueil</a>
            <a href="{{ route('agenda') }}">Nos animations</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>
    </div>
</header>
