@extends('layouts.public')

@section('content')
<main class="container" style="text-align: center; padding: 50px 20px;">
    <h1>Erreur 403 - Accès refusé</h1>
    <p>Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
    <a href="{{ route('accueil') }}" class="btn-primary">Retourner à l'accueil</a>
</main>
@endsection
