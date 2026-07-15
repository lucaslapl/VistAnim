@extends('layouts.public')

@section('content')
<div class="page-container">
    <h1>Politique de confidentialité</h1>
    <p class="text-muted">Dernière mise à jour : juin 2026</p>

    <section>
        <h2>1. Responsable du traitement</h2>
        <p>Le présent site est édité par <strong>{{ config('org.name') }}</strong>, situé <strong>{{ config('org.address') }}</strong>.</p>
        <p>Email : <strong>{{ config('org.email') }}</strong></p>
    </section>

    <section>
        <h2>2. Données personnelles collectées</h2>

        <h3>2.1 Inscription aux animations (public)</h3>
        <p>Lorsque vous vous inscrivez à une animation, nous collectons les données suivantes :</p>
        <ul>
            <li>Prénom et nom</li>
            <li>Adresse email</li>
            <li>Numéro de téléphone</li>
            <li>Nombre de participants</li>
            <li>Adresse IP (pour la sécurité et la limitation du taux de requêtes)</li>
        </ul>

        <h3>2.2 Compte administrateur / organisateur</h3>
        <p>Lors de la création d'un compte, nous collectons :</p>
        <ul>
            <li>Nom de l'organisation</li>
            <li>Adresse email</li>
            <li>Mot de passe (hashé — jamais stocké en clair)</li>
        </ul>

        <h3>2.3 Cookies</h3>
        <p>Ce site utilise uniquement un cookie technique de session nécessaire au fonctionnement de l'authentification et à la protection contre les attaques CSRF. Ce cookie est temporaire et est supprimé à la fermeture de votre navigateur.</p>
        <p>Aucun cookie de traçage publicitaire ou de réseau social n'est utilisé.</p>
    </section>

    <section>
        <h2>3. Finalités du traitement</h2>
        <p>Vos données sont collectées pour les finalités suivantes :</p>
        <ul>
            <li>Gestion des inscriptions aux animations nature</li>
            <li>Communication relative à l'animation (modification, annulation)</li>
            <li>Création et gestion des comptes administrateurs/organisateurs</li>
            <li>Sécurisation du site (protection CSRF, limitation du taux de requêtes)</li>
        </ul>
    </section>

    <section>
        <h2>4. Base légale du traitement</h2>
        <p>Les traitements effectués reposent sur les bases légales suivantes :</p>
        <ul>
            <li><strong>Votre consentement</strong> pour les données d'inscription aux animations (article 6.1.a du RGPD)</li>
            <li><strong>L'intérêt légitime</strong> pour la gestion des comptes administrateurs et la sécurisation du site (article 6.1.f du RGPD)</li>
        </ul>
    </section>

    <section>
        <h2>5. Destinataires des données</h2>
        <p>Vos données sont accessibles uniquement aux personnes suivantes :</p>
        <ul>
            <li>Les administrateurs et organisateurs habilités de {{ config('org.name') }}</li>
        </ul>
        <p>Aucune donnée n'est transmise à des tiers, à l'exception des obligations légales.</p>
    </section>

    <section>
        <h2>6. Durée de conservation</h2>
        <ul>
            <li><strong>Données d'inscription :</strong> conservées jusqu'à 3 ans après la dernière activité.</li>
            <li><strong>Données de compte administrateur :</strong> conservées tant que le compte est actif, supprimées dans un délai de 1 an après la désactivation.</li>
            <li><strong>Adresse IP :</strong> conservée 1 an à des fins de sécurité.</li>
            <li><strong>Cookie de session :</strong> durée de la session de navigation.</li>
        </ul>
    </section>

    <section>
        <h2>7. Vos droits</h2>
        <p>Conformément au RGPD et à la Loi Informatique et Libertés, vous disposez des droits suivants :</p>
        <ul>
            <li><strong>Droit d'accès</strong> — obtenir une copie de vos données</li>
            <li><strong>Droit de rectification</strong> — corriger des données inexactes</li>
            <li><strong>Droit à l'effacement</strong> — demander la suppression de vos données</li>
            <li><strong>Droit à la limitation</strong> du traitement</li>
            <li><strong>Droit à la portabilité</strong> — recevoir vos données dans un format réutilisable</li>
            <li><strong>Droit d'opposition</strong> — vous opposer au traitement de vos données</li>
        </ul>
        <p>Pour exercer vos droits, contactez-nous à <strong>{{ config('org.email') }}</strong></p>
    </section>

    <section>
        <h2>8. Sécurité</h2>
        <p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données personnelles contre tout accès non autorisé, modification, divulgation, perte ou destruction.</p>
    </section>

    <section>
        <h2>9. Réclamation auprès de la CNIL</h2>
        <p>Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une réclamation auprès de la <strong>Commission Nationale de l'Informatique et des Libertés (CNIL)</strong> :</p>
        <address>
            3 Place de Fontenoy, 75007 Paris<br>
            Tél. : 01 53 73 22 22<br>
            Site web : <a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer">www.cnil.fr</a>
        </address>
    </section>

    <section>
        <h2>10. Modification de la politique de confidentialité</h2>
        <p>Cette politique peut être mise à jour ponctuellement. La date de dernière mise à jour figure en haut de cette page.</p>
    </section>
</div>
@endsection
