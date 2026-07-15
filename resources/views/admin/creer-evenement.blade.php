@extends('layouts.admin')

@section('title', 'Créer une animation')

@php
$loadData = old() ?: ($draftData ?? []);
$isFromDraft = !empty($draftData);
@endphp

@section('content')
<div class="form-card">
    <h1>Créer une nouvelle animation nature</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($isFromDraft)
        <div class="alert alert-info" style="background:#e8f4fd;border:1px solid #b6d4fe;color:#0c5460;">
            📝 <strong>Brouillon en cours</strong> - Reprenez là où vous vous êtes arrêté.
            L'image devra être re-sélectionnée si besoin.
        </div>
    @endif

    <div id="draft-indicator" style="display:none;font-size:0.85em;color:#28a745;margin-bottom:10px;">
        💾 Brouillon enregistré à <span id="draft-saved-time"></span>
    </div>

    <form action="{{ route('admin.evenements.creer') }}" method="POST" enctype="multipart/form-data" id="event-form">
        @csrf
        <input type="hidden" name="draft_id" value="{{ $draftId ?? 0 }}">

        <div class="form-group">
            <label for="title">Titre de l'animation</label>
            <input type="text" name="title" id="title" required
                placeholder="Ex: Initiation à l'ornithologie au crépuscule"
                value="{{ $loadData['title'] ?? '' }}">
        </div>

        <div class="form-group">
            <label for="description">Description complète</label>
            <textarea name="description" id="description" required rows="6"
                placeholder="Décrivez le déroulement, le matériel à apporter...">{{ $loadData['description'] ?? '' }}</textarea>
        </div>

        <div class="form-group">
            <label>Dates et Horaires</label>
            <div id="dates_container">
                @php
                    $dates = $loadData['event_dates'] ?? [];
                @endphp
                @if (!empty($dates) && is_array($dates))
                    @foreach ($dates as $dt)
                        <div class="date-row">
                            <input type="datetime-local" name="event_dates[]" required style="flex: 1;" value="{{ $dt }}">
                            <button type="button" class="btn-remove-date">✕</button>
                        </div>
                    @endforeach
                @else
                    <div class="date-row">
                        <input type="datetime-local" name="event_dates[]" required style="flex: 1;" min="{{ now()->format('Y-m-d\TH:i') }}">
                        <button type="button" class="btn-remove-date" style="display:none;">✕</button>
                    </div>
                @endif
            </div>
            <button type="button" id="add_date_btn" class="btn-add-date">➕ Ajouter une date</button>
            <span class="form-help">Vous pouvez programmer jusqu'à 10 dates pour la même animation.</span>
        </div>

        <div class="form-group">
            <label for="max_participants">Jauge max (Participants)</label>
            <input type="number" name="max_participants" id="max_participants" min="1" required
                placeholder="Ex: 20"
                value="{{ old('max_participants', $loadData['max_participants'] ?? '') }}">
        </div>

        <div class="toggle-group">
            <label class="toggle-label">
                <input type="checkbox" id="toggle_min_participants" name="has_min_participants" value="1"
                    {{ !empty($loadData['has_min_participants']) ? 'checked' : '' }}>
                Cette animation nécessite un nombre minimum d'inscrits pour avoir lieu
            </label>
            <div id="min_participants_container" class="toggle-subsection"
                style="display: {{ !empty($loadData['has_min_participants']) ? 'block' : 'none' }};">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="min_participants">Nombre minimum de participants</label>
                    <input type="number" name="min_participants" id="min_participants" min="1"
                        value="{{ !empty($loadData['has_min_participants']) ? ($loadData['min_participants'] ?? 1) : 1 }}">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="location">Secteur de l'animation</label>
            <input type="text" name="location" id="location" required
                placeholder="Ex: Forêt de Vitré, Étang de la Loy..."
                value="{{ $loadData['location'] ?? '' }}">
        </div>

        <div class="form-group">
            <label for="rdv_point">Point de rendez-vous précis</label>
            <input type="text" name="rdv_point" id="rdv_point" required
                placeholder="Point GPS Google Maps"
                value="{{ $loadData['rdv_point'] ?? '' }}">
        </div>

        <div class="form-group">
            <label for="event_duration">Durée de l'animation</label>
            <input type="text" name="event_duration" id="event_duration" required
                placeholder="Ex: 2 heures"
                value="{{ $loadData['event_duration'] ?? '' }}">
        </div>

        <div class="form-group">
            <label for="audience_type">Public visé</label>
            <input type="text" name="audience_type" id="audience_type" required
                placeholder="Ex: Tout public, Familles, Enfants -12 ans, Adultes..."
                value="{{ $loadData['audience_type'] ?? '' }}">
        </div>

        <div class="form-section">
            <label>📁 Choisir une ou plusieurs catégories</label>
            <div class="flex gap-15 flex-wrap" style="margin-top: 10px;">
                @if ($categories->isEmpty())
                    <p style="font-style: italic; color: #888;">Aucune catégorie disponible. Ajoutez-en en base de données !</p>
                @else
                    @php $catIds = isset($loadData['categories']) ? array_map('intval', (array)$loadData['categories']) : []; @endphp
                    @foreach ($categories as $cat)
                        <label class="category-pill">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                {{ in_array((int)$cat->id, $catIds) ? 'checked' : '' }}>
                            {{ $cat->name }}
                        </label>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="form-section">
            <div class="toggle-group" style="margin-bottom: 0;">
                <label class="toggle-label">
                    <input type="checkbox" name="is_paid" id="is_paid" value="1"
                        {{ !empty($loadData['is_paid']) ? 'checked' : '' }}>
                    💵 Cette animation est payante
                </label>
            </div>
            <div id="price_details_zone" style="display: {{ !empty($loadData['is_paid']) ? 'block' : 'none' }}; margin-top: 12px;">
                <div class="form-group">
                    <label for="price_amount">💰 Prix par personne (en €)</label>
                    <input type="number" name="price_amount" id="price_amount" min="0.50" step="0.50"
                        placeholder="Ex: 5"
                        value="{{ $loadData['price_amount'] ?? '' }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="price_details">Modalités de paiement (gérées par votre structure)</label>
                    <textarea name="price_details" id="price_details" rows="3"
                        placeholder="Ex: 5€ par adulte / Gratuit -12 ans. Paiement par chèque ou espèces sur place.">{{ $loadData['price_details'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="image">Image illustrative</label>
            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp">
            <span class="form-help">Formats acceptés : JPEG, PNG, WebP (max 2 Mo).</span>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn-submit" style="flex:1;">🚀 Publier l'animation sur le site</button>
            <button type="submit" name="save_draft" value="1" class="btn-draft" style="flex:0 0 auto;" formnovalidate>
                💾 Sauvegarder le brouillon
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="{{ asset('assets/_js/event-form-common.js') }}" defer></script>
<script src="{{ asset('assets/_js/event-form-create.js') }}" defer></script>
@endpush
@endsection
