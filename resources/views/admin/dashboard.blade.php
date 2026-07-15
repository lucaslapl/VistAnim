@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('content')
<div class="dashboard-header">
    <h1>Tableau de bord de gestion</h1>
    <p>Bienvenue, <strong>{{ Auth::user()->name }}</strong> !</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number">{{ $stats['upcoming'] }}</div>
        <div class="stat-label">📅 Animations à venir</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $stats['participants'] }}</div>
        <div class="stat-label">👥 Inscriptions totales</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $stats['structures'] }}</div>
        <div class="stat-label">🏛️ Structures</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $stats['past'] }}</div>
        <div class="stat-label">✅ Animations passées</div>
    </div>
</div>

<div class="toolbar">
    <a href="{{ route('admin.evenements.creer') }}" class="btn-primary">➕ Créer une nouvelle animation nature</a>
</div>

@if ($drafts->isNotEmpty())
    <h2 class="section-title">📝 Mes brouillons</h2>
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Dernière modification</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($drafts as $d)
                    <tr>
                        <td><strong>{{ $d->draft_label ?? 'Sans titre' }}</strong></td>
                        <td>{{ $d->updated_at->format('d/m/Y H\hi') }}</td>
                        <td>
                            <a href="{{ route('admin.evenements.creer', ['draft_id' => $d->id]) }}" class="action-link" style="color:#28a745">✏️ Continuer</a> |
                            <a href="{{ route('admin.brouillons.supprimer', $d->id) }}" class="action-link" style="color:#dc3545"
                               onclick="return confirm('Supprimer ce brouillon ?')">🗑️ Supprimer</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if (Auth::user()->isAdmin())
    @php
        $allEvents = $otherEvents ?? collect();
    @endphp
    <h2 class="section-title">🟢 Toutes les animations de la ville</h2>
@else
    <h2 class="section-title">🌿 Mes animations</h2>
@endif

@php
    $myEvents = Auth::user()->isAdmin() ? ($otherEvents ?? collect()) : $ownEvents;
    $futureMyEvents = $myEvents->filter(fn($e) => $e->event_date->isFuture());
    $pastMyEvents = $myEvents->filter(fn($e) => $e->event_date->isPast());
@endphp

@if ($futureMyEvents->isEmpty() && $pastMyEvents->isEmpty())
    <p>Vous n'avez créé aucune animation pour le moment.</p>
@else
    @if ($futureMyEvents->isNotEmpty())
        <h3 class="section-title">📅 À venir ({{ $futureMyEvents->count() }})</h3>
        <div class="table-wrapper">
            <table class="admin-table sortable-table" data-table="my">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="date">Date<span class="sort-icon"></span></th>
                        <th class="sortable" data-sort="text">Titre<span class="sort-icon"></span></th>
                        <th class="sortable" data-sort="text">Lieu<span class="sort-icon"></span></th>
                        <th class="sortable" data-sort="capacity">Inscrits<span class="sort-icon"></span></th>
                        <th class="sortable" data-sort="text">Tarif<span class="sort-icon"></span></th>
                        @if (Auth::user()->isAdmin())
                            <th class="sortable" data-sort="text">Structure<span class="sort-icon"></span></th>
                        @endif
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($futureMyEvents as $event)
                        <tr>
                            <td data-date="{{ $event->event_date }}">{{ $event->event_date->format('d/m/Y H\hi') }}</td>
                            <td><strong>{{ $event->title }}</strong></td>
                            <td>{{ $event->location }}</td>
                            <td data-capacity="{{ $event->registrations_count }}">
                                <span class="capacity-text {{ $event->max_participants !== null && $event->registrations_count >= $event->max_participants ? 'full' : '' }}">
                                    {{ $event->registrations_count }}{{ $event->max_participants ? ' / ' . $event->max_participants : '' }}
                                </span>
                            </td>
                            <td>{{ $event->is_paid ? '💰 Payant' : '✅ Gratuit' }}</td>
                            @if (Auth::user()->isAdmin())
                                <td>{{ $event->organizer->name }}</td>
                            @endif
                            <td>
                                <a href="{{ route('admin.inscriptions.lister', $event->id) }}" class="action-link" style="color:#0056b3">👥 Inscrits</a> |
                                <a href="{{ route('admin.inscriptions.lister', ['eventId' => $event->id, 'export' => 'csv']) }}" class="action-link" style="color:#28a745">📥 CSV</a> |
                                <a href="{{ route('admin.evenements.modifier', $event->id) }}" class="action-link" style="color:#e0a800">✏️ Modifier</a> |
                                <a href="{{ route('admin.evenements.dupliquer', $event->id) }}" class="action-link" style="color:#6f42c1">📋 Dupliquer</a> |
                                <a href="{{ route('admin.evenements.supprimer', $event->id) }}" class="action-link" style="color:#dc3545"
                                   onclick="return confirm('Supprimer cette animation et toutes ses inscriptions ?')">❌ Supprimer</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($pastMyEvents->isNotEmpty())
        <div class="past-section">
            <h3 class="section-title">📅 Passées ({{ $pastMyEvents->count() }})</h3>
            <div class="table-wrapper">
                <table class="admin-table sortable-table" data-table="my">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="date">Date<span class="sort-icon"></span></th>
                            <th class="sortable" data-sort="text">Titre<span class="sort-icon"></span></th>
                            <th class="sortable" data-sort="text">Lieu<span class="sort-icon"></span></th>
                            <th class="sortable" data-sort="capacity">Inscrits<span class="sort-icon"></span></th>
                            <th class="sortable" data-sort="text">Tarif<span class="sort-icon"></span></th>
                            @if (Auth::user()->isAdmin())
                                <th class="sortable" data-sort="text">Structure<span class="sort-icon"></span></th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pastMyEvents as $event)
                            <tr>
                                <td data-date="{{ $event->event_date }}">{{ $event->event_date->format('d/m/Y H\hi') }}</td>
                                <td><strong>{{ $event->title }}</strong></td>
                                <td>{{ $event->location }}</td>
                                <td data-capacity="{{ $event->registrations_count }}">
                                    <span class="capacity-text">
                                        {{ $event->registrations_count }}{{ $event->max_participants ? ' / ' . $event->max_participants : '' }}
                                    </span>
                                </td>
                                <td>{{ $event->is_paid ? '💰 Payant' : '✅ Gratuit' }}</td>
                                @if (Auth::user()->isAdmin())
                                    <td>{{ $event->organizer->name }}</td>
                                @endif
                                <td>
                                    <a href="{{ route('admin.inscriptions.lister', $event->id) }}" class="action-link" style="color:#0056b3">👥 Inscrits</a> |
                                    <a href="{{ route('admin.inscriptions.lister', ['eventId' => $event->id, 'export' => 'csv']) }}" class="action-link" style="color:#28a745">📥 CSV</a> |
                                    <a href="{{ route('admin.evenements.modifier', $event->id) }}" class="action-link" style="color:#e0a800">✏️ Modifier</a> |
                                    <a href="{{ route('admin.evenements.dupliquer', $event->id) }}" class="action-link" style="color:#6f42c1">📋 Dupliquer</a> |
                                    <a href="{{ route('admin.evenements.supprimer', $event->id) }}" class="action-link" style="color:#dc3545"
                                       onclick="return confirm('Supprimer cette animation et toutes ses inscriptions ?')">❌ Supprimer</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif

@if (!Auth::user()->isAdmin() && (($otherEvents ?? collect())->isNotEmpty()))
    @php
        $futureOther = collect($otherEvents)->filter(fn($e) => $e->event_date->isFuture());
        $pastOther = collect($otherEvents)->filter(fn($e) => $e->event_date->isPast());
    @endphp
    @if ($futureOther->isNotEmpty() || $pastOther->isNotEmpty())
        <div class="other-section">
            <h2 class="section-title">📅 Calendrier des autres structures (Lecture seule)</h2>
            <p style="font-style: italic; color: #6c757d;">Cette liste vous permet de consulter les animations prévues par vos collègues pour éviter les doublons sur le terrain.</p>

            @if ($futureOther->isNotEmpty())
                <h3 class="section-title" style="margin-top:20px;">📅 À venir ({{ $futureOther->count() }})</h3>
                <div class="table-wrapper">
                    <table class="admin-table sortable-table" data-table="other">
                        <thead>
                            <tr>
                                <th class="sortable" data-sort="date">Date<span class="sort-icon"></span></th>
                                <th class="sortable" data-sort="text">Titre<span class="sort-icon"></span></th>
                                <th class="sortable" data-sort="text">Lieu<span class="sort-icon"></span></th>
                                <th class="sortable" data-sort="capacity">Inscrits<span class="sort-icon"></span></th>
                                <th class="sortable" data-sort="text">Structure organisatrice<span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($futureOther as $event)
                                <tr>
                                    <td data-date="{{ $event->event_date }}">{{ $event->event_date->format('d/m/Y H\hi') }}</td>
                                    <td>{{ $event->title }}</td>
                                    <td>{{ $event->location }}</td>
                                    <td data-capacity="{{ $event->registrations_count ?? 0 }}">
                                        <span class="capacity-text {{ $event->max_participants !== null && ($event->registrations_count ?? 0) >= $event->max_participants ? 'full' : '' }}">
                                            {{ $event->registrations_count ?? 0 }}{{ $event->max_participants ? ' / ' . $event->max_participants : '' }}
                                        </span>
                                    </td>
                                    <td><strong>{{ $event->organizer->name }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($pastOther->isNotEmpty())
                <div class="past-section">
                    <h3 class="section-title">📅 Passées ({{ $pastOther->count() }})</h3>
                    <div class="table-wrapper">
                        <table class="admin-table sortable-table" data-table="other">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="date">Date<span class="sort-icon"></span></th>
                                    <th class="sortable" data-sort="text">Titre<span class="sort-icon"></span></th>
                                    <th class="sortable" data-sort="text">Lieu<span class="sort-icon"></span></th>
                                    <th class="sortable" data-sort="capacity">Inscrits<span class="sort-icon"></span></th>
                                    <th class="sortable" data-sort="text">Structure organisatrice<span class="sort-icon"></span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pastOther as $event)
                                    <tr>
                                        <td data-date="{{ $event->event_date }}">{{ $event->event_date->format('d/m/Y H\hi') }}</td>
                                        <td>{{ $event->title }}</td>
                                        <td>{{ $event->location }}</td>
                                        <td data-capacity="{{ $event->registrations_count ?? 0 }}">
                                            <span class="capacity-text">
                                                {{ $event->registrations_count ?? 0 }}{{ $event->max_participants ? ' / ' . $event->max_participants : '' }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $event->organizer->name }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif
@endif

@push('styles')
<style>
    th.sortable { cursor: pointer; user-select: none; }
    th.sortable:hover { background: #e9ecef; }
    th.sortable .sort-icon { font-size: 0.7em; margin-left: 4px; opacity: 0.4; }
    th.sortable.asc .sort-icon::after { content: " ▲"; opacity: 1; }
    th.sortable.desc .sort-icon::after { content: " ▼"; opacity: 1; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/_js/table-sort.js') }}" defer></script>
<script src="{{ asset('assets/_js/admin-utils.js') }}" defer></script>
@endpush
@endsection
