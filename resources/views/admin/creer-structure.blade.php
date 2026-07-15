@extends('layouts.admin')

@section('title', 'Créer une structure')

@section('content')
<div class="form-card">
    <h1>Créer une nouvelle structure</h1>
    <p>Les identifiants permettront à la structure de se connecter à son espace pour créer et gérer ses propres animations.</p>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @else
        <form action="{{ route('admin.structures.creer') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Nom de la structure / association</label>
                <input type="text" name="name" id="name" required placeholder="Ex: LPO, Bretagne Vivante, CPIE..." value="{{ old('name') }}">
            </div>

            <div class="form-group">
                <label for="email">Adresse email de connexion</label>
                <input type="email" name="email" id="email" required placeholder="contact@association.fr" value="{{ old('email') }}">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe (min. 8 caractères)</label>
                <input type="password" name="password" id="password" required minlength="8" placeholder="********">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" placeholder="********">
            </div>

            <div class="form-section">
                <p style="font-style:italic;color:#6c757d;">
                    📌 La structure aura le rôle <strong>"Organisateur"</strong> : elle pourra créer, modifier et supprimer ses propres animations, et consulter celles des autres en lecture seule.
                </p>
            </div>

            <button type="submit" class="btn-submit">Créer la structure 🌿</button>
        </form>

        <p style="margin-top: 20px;">
            <a href="{{ route('admin.structures.lister') }}" style="color: #6c757d;">⬅️ Retour à la gestion des structures</a>
        </p>
    @endif
</div>
@endsection
