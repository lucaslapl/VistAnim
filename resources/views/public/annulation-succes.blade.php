@extends('layouts.public')

@section('content')
<main class="container">
    <div class="alert alert-success">
        <h1>Votre inscription a bien été annulée.</h1>
        <p>Les places ont été libérées pour le public. Merci de nous avoir prévenu ! 🌿</p>
        @if ($registration->payment_status === 'paid')
            <p>💳 Le remboursement a été initié. Il apparaîtra sous 5 à 10 jours ouvrés sur votre compte.</p>
        @endif
        <a href="{{ route('accueil') }}">Retour à l'accueil</a>
    </div>
</main>
@endsection
