@extends('layouts.admin')

@section('title', 'Gestion des structures')

@section('content')
<div class="dashboard-header">
    <h1>Gestion des structures</h1>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="toolbar">
    <a href="{{ route('admin.structures.creer') }}" class="btn-primary">➕ Créer une nouvelle structure</a>
</div>

@if ($users->isEmpty())
    <p>Aucune structure enregistrée pour le moment.</p>
@else
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom de la structure</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Animations</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                        <td>
                            @if ($user->role === 'admin')
                                <span class="badge badge-admin">Administrateur</span>
                            @else
                                <span class="badge badge-organisateur">Organisateur</span>
                            @endif
                        </td>
                        <td>{{ $user->events_count }}</td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if ($user->id !== Auth::id())
                                <a href="{{ route('admin.structures.supprimer', $user->id) }}"
                                   class="action-link" style="color:#dc3545"
                                   onclick="return confirm('⚠️ Supprimer {{ $user->name }} ?\n\nToutes ses animations et inscriptions seront définitivement supprimées.\n\nCette action est irréversible.')">
                                    🗑️ Supprimer
                                </a>
                            @else
                                <span style="color:#6c757d;font-style:italic;">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@push('scripts')
<script src="{{ asset('assets/_js/admin-utils.js') }}" defer></script>
@endpush
@endsection
