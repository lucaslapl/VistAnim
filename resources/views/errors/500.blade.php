@extends('layouts.public')

@section('content')
<main class="container" style="text-align: center; padding: 50px 20px;">
    <h1>Erreur 500 - Erreur interne</h1>
    <p>Une erreur technique est survenue. Veuillez réessayer plus tard.</p>
    <a href="{{ route('accueil') }}" class="btn-primary">Retourner à l'accueil</a>
</main>
@endsection
