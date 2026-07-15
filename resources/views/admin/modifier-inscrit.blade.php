@extends('layouts.admin')

@section('title', 'Modifier le participant')

@php
$maxPossible = $event->max_participants - ($event->reserved_places - $registration->nb_participants);
@endphp

@section('content')
<div style="max-width: 600px; margin: 30px auto; padding: 20px; border: 1px solid #ccc; font-family: sans-serif; box-sizing: border-box; width: 100%;">
    <h1>Gérer le participant</h1>
    <h2>Animation : {{ $registration->event->title }}</h2>

    @if ($errors->any())
        <p style="color: red; font-weight: bold;">{{ $errors->first() }}</p>
    @endif
    @if (session('success'))
        <p style="color: green; font-weight: bold;">{{ session('success') }}</p>
    @endif

    <form action="{{ route('admin.inscriptions.modifier', ['event' => $event->id, 'registration' => $registration->id]) }}" method="POST" style="margin-bottom: 40px;">
        @csrf
        <div style="margin-bottom: 15px;">
            <label for="firstname">Prénom :</label><br>
            <input type="text" name="firstname" id="firstname" value="{{ $registration->firstname }}" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="lastname">Nom :</label><br>
            <input type="text" name="lastname" id="lastname" value="{{ $registration->lastname }}" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email">Adresse Email :</label><br>
            <input type="email" name="email" id="email" value="{{ $registration->email }}" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="phone">Téléphone :</label><br>
            <input type="tel" name="phone" id="phone" value="{{ $registration->phone }}" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="nb_participants">Nombre de places réservées :</label><br>
            <input type="number" name="nb_participants" id="nb_participants" value="{{ $registration->nb_participants }}" min="1" max="{{ $maxPossible }}" required style="width: 100%; padding: 8px;">
            <small style="color: #6c757d;">(Maximum physiquement possible par rapport à la jauge : {{ $maxPossible }})</small>
        </div>

        <button type="submit" style="padding: 10px 15px; background: #0056b3; color: white; border: none; cursor: pointer; font-weight: bold;">
            Appliquer les modifications 💾
        </button>
    </form>

    <div style="background: #fdf3f3; padding: 15px; border: 1px solid #f5c6cb;">
        <h3 style="color: #721c24; margin-top: 0;">Danger Zone : Désinscrire le citoyen</h3>
        <p style="font-size: 14px; margin-bottom: 15px;">
            Cette action annulera immédiatement l'inscription de cette personne et libérera ses places pour le grand public.<br>
            Si l'animation est payante, le remboursement sera effectué automatiquement.
        </p>
        <a href="{{ route('admin.inscriptions.modifier', ['event' => $event->id, 'registration' => $registration->id, 'action' => 'supprimer']) }}"
            style="background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; font-weight: bold; font-size: 14px;"
            onclick="return confirm('Supprimer définitivement l\'inscription de ce citoyen ?')">
            Désinscrire ce participant ❌
        </a>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/_js/admin-utils.js') }}" defer></script>
@endpush
@endsection
