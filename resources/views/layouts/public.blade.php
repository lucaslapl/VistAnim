<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $pageDescription ?? config('org.tagline') }}">
    <meta name="author" content="{{ config('org.name') }}">

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌿</text></svg>">

    <meta property="og:title" content="{{ isset($pageTitle) ? $pageTitle . ' | ' . config('app.name') : config('app.name') }}">
    <meta property="og:description" content="{{ $pageDescription ?? config('org.tagline') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="fr_FR">

    <title>{{ isset($pageTitle) ? $pageTitle . ' | ' . config('app.name') : config('app.name') }}</title>

    <link rel="stylesheet" href="{{ asset('assets/_css/main.css') }}">

    @include('includes.analytics')
</head>
<body>

    <div class="demo-banner">
        ⚠️ Site de démonstration - Les données entrées sont réinitialisées toutes les 24h.
    </div>

    @include('includes.header')

    @yield('content')

    @include('includes.partners')
    @include('includes.footer')

    <script src="{{ asset('assets/_js/nav.js') }}" defer></script>
</body>
</html>
