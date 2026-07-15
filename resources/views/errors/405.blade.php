@extends('layouts.public')

@section('content')
<main class="container" style="text-align: center; padding: 50px 20px;">
    <h1>Erreur 405 - Méthode non autorisée</h1>
    <p>Cette action n'est pas autorisée via cette méthode de requête.</p>
    <a href="{{ route('accueil') }}" class="btn-primary">Retourner à l'accueil</a>
</main>
@endsection
