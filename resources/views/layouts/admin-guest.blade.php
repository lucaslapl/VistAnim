<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Connexion') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/_css/main.css') }}">
    @stack('styles')

    @include('includes.analytics')
</head>
<body>
    <main class="admin-dashboard">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>