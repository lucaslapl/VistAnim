@extends('layouts.public')

@section('content')
<div class="page-container">
    <h1>Mentions légales</h1>
    <p class="text-muted">Dernière mise à jour : juin 2026</p>

    <section>
        <h2>1. Éditeur du site</h2>
        <p>Le site <strong>{{ config('app.name') }}</strong> est édité par :</p>
        <p>
            <strong>{{ config('org.name') }}</strong><br>
            {{ config('org.address') }}<br>
            Email : {{ config('org.email') }}<br>
            Téléphone : {{ config('org.phone') }}
        </p>
    </section>

    <section>
        <h2>2. Directeur de la publication</h2>
        <p>{{ config('org.director') ?: '[Nom et prénom du directeur de la publication]' }}</p>
    </section>

    <section>
        <h2>3. Hébergement</h2>
        <p>Le site est hébergé par :</p>
        <p>
            <strong>{{ config('org.host_name') ?: '[Nom de l\'hébergeur]' }}</strong><br>
            {{ config('org.host_address') ?: '[Adresse de l\'hébergeur]' }}
        </p>
    </section>

    <section>
        <h2>4. Propriété intellectuelle</h2>
        <p>L'ensemble des contenus présents sur ce site (textes, images, photographies, logos, icônes) est protégé par le droit d'auteur et par le code de la propriété intellectuelle. Toute reproduction, représentation, modification ou exploitation, totale ou partielle, est interdite sans autorisation préalable.</p>
    </section>

    <section>
        <h2>5. Données personnelles</h2>
        <p>La collecte et le traitement de vos données personnelles sont régis par notre <a href="{{ route('politique-confidentialite') }}">Politique de confidentialité</a>.</p>
    </section>

    <section>
        <h2>6. Cookies</h2>
        <p>Ce site utilise uniquement un cookie technique de session nécessaire à son fonctionnement. Aucun cookie de traçage ou publicitaire n'est déposé. Consultez notre <a href="{{ route('politique-confidentialite') }}">Politique de confidentialité</a> pour en savoir plus.</p>
    </section>

    <section>
        <h2>7. Limitation de responsabilité</h2>
        <p>L'éditeur du site s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées, sans pouvoir garantir leur exhaustivité. L'éditeur ne saurait être tenu responsable des dommages directs ou indirects résultant de l'utilisation du site.</p>
    </section>
</div>
@endsection
