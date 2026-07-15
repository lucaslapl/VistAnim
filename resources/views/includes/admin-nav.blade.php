<div class="demo-banner">
    ⚠️ Site de démonstration - Les données entrées sont réinitialisées toutes les 24h.
</div>
<nav class="admin-nav">
    <div class="admin-nav-inner">

        <div class="admin-nav-links">
            <span class="admin-brand">🛠️ Console Admin</span>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link" style="font-weight:bold;">📊 Tableau de bord</a>
            <a href="{{ route('admin.evenements.creer') }}" class="admin-nav-link">➕ Nouvelle Animation</a>
            @if (Auth::user()->isAdmin())
                <a href="{{ route('admin.structures.lister') }}" class="admin-nav-link">🏛️ Structures</a>
            @endif
            <a href="{{ route('accueil') }}" target="_blank" class="admin-nav-link muted">👁️ Voir le site public</a>
        </div>

        <div class="admin-user-info">
            <span class="admin-user-badge">
                👤 <strong>{{ Auth::user()->name }}</strong>
                (<span class="admin-user-role">{{ ucfirst(Auth::user()->role) }}</span>)
            </span>
            <form action="{{ route('admin.logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn-logout" style="background:none;border:none;cursor:pointer;color:white;">👋 Déconnexion</button>
            </form>
        </div>

    </div>
</nav>
