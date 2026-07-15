@extends('layouts.public')

@section('content')
<main class="container" style="text-align: center; padding: 50px 20px;">
    <h1>Erreur 404 - Ressource non trouvée</h1>
    <p>La ressource demandée n'a pas été trouvée. Veuillez vérifier l'URL et réessayer.</p>
    <a href="{{ route('accueil') }}" class="btn-primary">Retourner à l'accueil</a>
</main>
@endsection
