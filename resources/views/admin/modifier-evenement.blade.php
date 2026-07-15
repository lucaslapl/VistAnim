@extends('layouts.admin')

@section('title', 'Modifier : ' . $event->title)

@section('content')
<div class="form-card">
    <h1>Modifier l'animation : {{ $event->title }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.evenements.modifier', $event->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="title">Titre de l'animation</label>
            <input type="text" name="title" id="title" value="{{ $event->title }}" required placeholder="Ex: Initiation à l'ornithologie au crépuscule">
        </div>

        <div class="form-group">
            <label for="description">Description complète</label>
            <textarea name="description" id="description" required rows="6" placeholder="Décrivez le déroulement, le matériel à apporter...">{{ $event->description }}</textarea>
        </div>

        <div class="form-group">
            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label for="event_date">Date et Heure</label>
                    <input type="datetime-local" name="event_date" id="event_date" value="{{ $event->event_date->format('Y-m-d\TH:i') }}" required>
                </div>
                <div style="flex: 1;">
                    <label for="max_participants">Jauge max (Participants)</label>
                    <input type="number" name="max_participants" id="max_participants" value="{{ $event->max_participants }}" min="1" required placeholder="Ex: 20">
                </div>
            </div>
        </div>

        <div class="toggle-group">
            <label class="toggle-label">
                <input type="checkbox" id="toggle_min_participants" name="has_min_participants" value="1" {{ $event->min_participants > 0 ? 'checked' : '' }}>
                Cette animation nécessite un nombre minimum d'inscrits pour avoir lieu
            </label>
            <div id="min_participants_container" class="toggle-subsection" style="display: {{ $event->min_participants > 0 ? 'block' : 'none' }};">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="min_participants">Nombre minimum de participants</label>
                    <input type="number" name="min_participants" id="min_participants" min="1" value="{{ $event->min_participants > 0 ? $event->min_participants : 1 }}">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="location">Secteur de l'animation</label>
            <input type="text" name="location" id="location" value="{{ $event->location }}" required placeholder="Ex: Forêt de Vitré, Étang de la Loy...">
        </div>

        <div class="form-group">
            <label for="rdv_point">Point de rendez-vous précis</label>
            <input type="text" name="rdv_point" id="rdv_point" value="{{ $event->rdv_point }}" required placeholder="Point GPS Google Maps">
        </div>

        <div class="form-group">
            <label for="event_duration">Durée de l'animation</label>
            <input type="text" name="event_duration" id="event_duration" value="{{ $event->event_duration }}" required placeholder="Ex: 2 heures">
        </div>

        <div class="form-group">
            <label for="audience_type">Public visé</label>
            <input type="text" name="audience_type" id="audience_type" value="{{ $event->audience_type }}" required placeholder="Ex: Tout public, Familles, Enfants -12 ans, Adultes...">
        </div>

        <div class="form-section">
            <label>📁 Catégories de l'animation</label>
            <div class="flex gap-15 flex-wrap" style="margin-top: 10px;">
                @if ($categories->isEmpty())
                    <p style="font-style: italic; color: #888;">Aucune catégorie configurée dans le système.</p>
                @else
                    @php $currentCatIds = $event->categories->pluck('id')->toArray(); @endphp
                    @foreach ($categories as $cat)
                        <label class="category-pill">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}" {{ in_array($cat->id, $currentCatIds) ? 'checked' : '' }}>
                            {{ $cat->name }}
                        </label>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="form-section">
            <div class="toggle-group" style="margin-bottom: 0;">
                <label class="toggle-label">
                    <input type="checkbox" name="is_paid" id="is_paid" value="1" {{ $event->is_paid ? 'checked' : '' }}>
                    💵 Cette animation est payante
                </label>
            </div>
            <div id="price_details_zone" style="margin-top: 12px; display: {{ $event->is_paid ? 'block' : 'none' }};">
                <div class="form-group">
                    <label for="price_amount">💰 Prix par personne (en €)</label>
                    <input type="number" name="price_amount" id="price_amount" min="0.50" step="0.50"
                        placeholder="Ex: 5" value="{{ $event->price_amount }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="price_details">Modalités de paiement (gérées par votre structure)</label>
                    <textarea name="price_details" id="price_details" rows="3" placeholder="Ex: 5€ par adulte / Gratuit -12 ans. Paiement par chèque ou espèces sur place.">{{ $event->price_details }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-section">
            <label>Image illustrative</label>
            <div class="image-preview">
                <img src="{{ asset('assets/images/animations/' . ($event->image ?: 'placeholder.svg')) }}" alt="Image actuelle">
            </div>
            @if ($event->image)
                <label class="remove-image-label">
                    <input type="checkbox" name="remove_image" value="1">
                    Supprimer l'image actuelle
                </label>
                <hr style="margin: 12px 0; border: none; border-top: 1px solid #e2e8f0;">
            @endif
            <div class="form-group" style="margin-bottom: 0;">
                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp">
                <span class="form-help">Laissez vide pour conserver l'image actuelle. Formats : JPEG, PNG, WebP (max 2 Mo).</span>
            </div>
        </div>

        <button type="submit" class="btn-submit">Enregistrer les modifications 💾</button>
    </form>
</div>

@push('scripts')
<script src="{{ asset('assets/_js/event-form-common.js') }}" defer></script>
@endpush
@endsection
