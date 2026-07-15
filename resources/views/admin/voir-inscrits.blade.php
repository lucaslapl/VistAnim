@extends('layouts.admin')

@section('title', 'Inscrits - ' . $event->title)

@section('content')
<div class="inscrits-container">
    <div class="event-header">
        <h1>Liste des participants</h1>
        <h2>Animation : <span class="event-title">{{ $event->title }}</span></h2>

        <p class="event-meta">
            📅 <strong>Date :</strong> {{ $event->event_date->format('d/m/Y à H\hi') }}<br>
            📍 <strong>Lieu de RDV :</strong> {{ $event->location }}<br>
            📊 <strong>État de la jauge :</strong>
            <span class="gauge {{ $event->reserved_places >= $event->max_participants ? 'full' : 'available' }}">
                {{ $event->reserved_places }}
            </span>
            place{{ $event->reserved_places > 1 ? 's' : '' }} réservée{{ $event->reserved_places > 1 ? 's' : '' }}
            sur un maximum de {{ $event->max_participants }}.
        </p>
    </div>

    <hr class="event-divider">

    @if ($registrations->isEmpty())
        <p class="empty-state">
            Aucun citoyen ne s'est encore inscrit à cette animation pour le moment. 🌿
        </p>
    @else
        <div class="table-wrapper">
            <table class="admin-table inscrits-table">
                <thead>
                    <tr>
                        <th>Nom / Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Places réservées</th>
                        <th>Paiement</th>
                        <th>Date d'inscription</th>
                        <th class="no-print">Gestion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registrations as $inscrit)
                        <tr>
                            <td><strong>{{ $inscrit->lastname }}</strong> {{ $inscrit->firstname }}</td>
                            <td><a href="mailto:{{ $inscrit->email }}">{{ $inscrit->email }}</a></td>
                            <td><code>{{ $inscrit->phone }}</code></td>
                            <td><span class="places-badge">{{ $inscrit->nb_participants }}</span></td>
                            <td>
                                @if ($event->is_paid)
                                    @switch($inscrit->payment_status)
                                        @case('paid')
                                            <span style="color: green; font-weight: bold;">✅ Payé</span>
                                            @break
                                        @case('pending')
                                            <span style="color: orange;">⏳ En attente</span>
                                            @break
                                        @case('failed')
                                            <span style="color: red;">❌ Échec</span>
                                            @break
                                        @case('refunded')
                                            <span style="color: purple;">↩️ Remboursé</span>
                                            @break
                                        @default
                                            <span style="color: gray;">—</span>
                                    @endswitch
                                @else
                                    <span style="color: gray;">—</span>
                                @endif
                            </td>
                            <td class="date-cell">{{ $inscrit->registered_at->format('d/m/Y à H\hi') }}</td>
                            <td class="no-print">
                                <a href="{{ route('admin.inscriptions.modifier', ['eventId' => $event->id, 'registrationId' => $inscrit->id]) }}" class="action-link" style="color:#0056b3">⚙️ Administrer</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="print-toolbar">
            <button data-action="print" class="btn-primary no-print" style="background:#6c757d;">🖨️ Imprimer la liste pour le terrain</button>
            <a href="{{ route('admin.inscriptions.lister', ['eventId' => $event->id, 'export' => 'csv']) }}" class="btn-primary no-print" style="background:#28a745; text-decoration:none;">📥 Exporter en CSV</a>
        </div>
    @endif
</div>

@push('scripts')
<script src="{{ asset('assets/_js/admin-utils.js') }}" defer></script>
@endpush
@endsection
