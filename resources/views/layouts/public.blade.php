<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $pageDescription ?? config('org.tagline') }}">
    <meta name="author" content="{{ config('org.name') }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#ffffff">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Reem+Kufi:wght@400;500;600;700&display=swap" rel="stylesheet">

    <meta property="og:title" content="{{ isset($pageTitle) ? $pageTitle . ' | ' . config('app.name') : config('app.name') }}">
    <meta property="og:description" content="{{ $pageDescription ?? config('org.tagline') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:image" content="{{ asset('images/logos/vistanim-ogbanner.webp') }}">
    <meta property="og:image:type" content="image/webp">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

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
