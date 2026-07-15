@extends('layouts.public')

@section('content')
<div class="page-container">
    <h1>Conditions générales d'utilisation</h1>
    <p class="text-muted">Dernière mise à jour : juin 2026</p>

    <section>
        <h2>1. Objet</h2>
        <p>
            Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation
            du site <strong>{{ config('app.name') }}</strong> édité par <strong>{{ config('org.name') }}</strong>.
        </p>
        <p>En utilisant ce site, vous acceptez pleinement et sans réserve les présentes CGU.</p>
    </section>

    <section>
        <h2>2. Accès au site</h2>
        <p>Le site est accessible gratuitement à tout utilisateur disposant d'un accès à internet. L'éditeur se réserve le droit de suspendre ou interdire l'accès au site en cas de non-respect des présentes CGU.</p>
    </section>

    <section>
        <h2>3. Inscription aux animations</h2>
        <p>L'inscription à une animation implique :</p>
        <ul>
            <li>La fourniture d'informations exactes (nom, email, téléphone, nombre de participants)</li>
            <li>L'acceptation de la <a href="{{ route('politique-confidentialite') }}">politique de confidentialité</a></li>
            <li>Le respect du nombre maximal de participants indiqué</li>
            <li>La possibilité de modifier ou annuler son inscription via le lien reçu par email</li>
        </ul>
        <p>L'organisateur se réserve le droit d'annuler une animation en cas de force majeure ou de nombre insuffisant de participants. Les inscrits en seront informés par email.</p>
    </section>

    <section>
        <h2>4. Paiement</h2>
        <p>Certaines animations peuvent être payantes. Le paiement s'effectue en ligne par carte bancaire via le prestataire Stripe. Aucune donnée bancaire n'est stockée sur nos serveurs.</p>
        <p>En cas d'annulation par l'organisateur, le remboursement intégral est effectué sous 14 jours. En cas d'annulation par le participant, les conditions de remboursement sont précisées dans la confirmation d'inscription.</p>
    </section>

    <section>
        <h2>5. Comptes administrateurs</h2>
        <p>Les comptes administrateurs et organisateurs sont nominatifs. L'utilisateur s'engage à :</p>
        <ul>
            <li>Ne pas partager ses identifiants de connexion</li>
            <li>Ne pas créer de faux événements ou de contenus inappropriés</li>
            <li>Respecter la confidentialité des données des participants</li>
        </ul>
    </section>

    <section>
        <h2>6. Propriété intellectuelle</h2>
        <p>L'ensemble du contenu du site (textes, graphismes, logos, icônes) est protégé par le droit d'auteur. Toute reproduction ou représentation est interdite sans autorisation préalable.</p>
    </section>

    <section>
        <h2>7. Responsabilité</h2>
        <p>L'éditeur s'efforce d'assurer la disponibilité et l'exactitude des informations du site, sans garantie absolue. L'éditeur ne saurait être tenu responsable des dommages directs ou indirects résultant de l'utilisation du site.</p>
    </section>

    <section>
        <h2>8. Données personnelles</h2>
        <p>Le traitement des données personnelles est régi par notre <a href="{{ route('politique-confidentialite') }}">Politique de confidentialité</a> conformément au Règlement Général sur la Protection des Données (RGPD).</p>
    </section>

    <section>
        <h2>9. Modification des CGU</h2>
        <p>Les présentes CGU peuvent être modifiées à tout moment. Les utilisateurs sont invités à les consulter régulièrement. La date de dernière mise à jour figure en haut de cette page.</p>
    </section>

    <section>
        <h2>10. Droit applicable</h2>
        <p>Les présentes CGU sont régies par le droit français. En cas de litige, les parties s'efforceront de trouver une solution amiable avant toute action judiciaire.</p>
    </section>
</div>
@endsection
