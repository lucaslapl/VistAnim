@extends('layouts.admin-guest')

@section('title', 'Connexion')

@section('content')
<div class="login-page" style="max-width: 400px; margin: 40px auto; text-align: center;">
    <h2>Espace Organisateur &amp; Admin</h2>

    @if ($errors->any())
        <p class="login-error" style="color: #dc3545;">{{ $errors->first() }}</p>
    @endif

    <form action="{{ route('admin.login') }}" method="POST" class="login-form">
        @csrf
        <div class="form-group">
            <label for="email">Adresse Email :</label>
            <input type="email" name="email" id="email" required class="form-input" value="{{ old('email') }}">
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required class="form-input">
        </div>

        <button type="submit" class="btn-login">Se connecter</button>
    </form>

    <p><a href="{{ route('accueil') }}" class="back-link">⬅️ Retour au site public</a></p>
</div>
@endsection
