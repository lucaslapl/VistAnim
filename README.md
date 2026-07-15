# VistAnim

> Plateforme de gestion d'animations nature pour un Atlas de la Biodiversité Communale (ABC).

VistAnim permet à une structure (association, collectivité) de publier des animations nature, de gérer les inscriptions du public (gratuites ou payantes via Stripe), et d'administrer l'ensemble depuis un back-office avec gestion des rôles.

## Fonctionnalités

### Côté public
- Page d'accueil et agenda des animations
- Fiche détaillée par animation
- Inscription en ligne avec confirmation par email
- Paiement en ligne sécurisé (Stripe) pour les animations payantes
- Gestion de sa réservation via un lien à usage unique (token)
- Récupération de ticket par email
- Pages légales : CGU, mentions légales, politique de confidentialité, contact

### Côté administration
- Authentification personnalisée avec rôles (`admin`, `organisateur`)
- Tableau de bord (statistiques, vue d'ensemble)
- Création, modification, duplication et suppression d'animations
- Système de brouillons pour les animations en préparation
- Gestion des inscriptions par événement (consultation, modification)
- Gestion des structures/utilisateurs (réservé aux admins)
- Journal d'audit des actions d'administration (`AdminLog`)
- Envoi automatique de rappels J-1 aux participants et à l'organisateur

## Stack technique

| Domaine | Technologie |
|---|---|
| Framework | Laravel 13 |
| Langage | PHP 8.3+ |
| Base de données | MySQL / MariaDB |
| Paiement | Stripe (`stripe/stripe-php`) |
| Frontend | Vite 8, Tailwind CSS, Alpine.js, Sass |
| Emails | Mailables asynchrones (`ShouldQueue`) |
| Files d'attente / cache / sessions | Driver `database` |
| Qualité | Pint, PHPUnit |

## Architecture

```
app/
├── Console/Commands/     # SendReminders (reminders:send)
├── Http/Controllers/     # Contrôleurs publics et admin
├── Mail/                 # 9 Mailables (confirmations, rappels, annulations…)
├── Models/               # AdminLog, Category, Event, EventDraft, Registration, User
└── Services/             # StripeService, MailService

config/
├── org.php               # Configuration de la structure (nom, contacts…)
├── stripe.php            # Clés Stripe
└── services.php          # Services tiers

database/
├── migrations/           # Schéma (users, events, categories, registrations, drafts, logs…)
├── factories/
└── seeders/              # DemoSeeder (données de démonstration)

resources/views/
├── admin/                # Back-office
├── public/               # Pages publiques
├── emails/               # Templates d'emails
├── layouts/ includes/ errors/

routes/
├── web.php               # Routes web (public + admin + webhook Stripe)
└── console.php           # Planification (reminders:send toutes les 30 min)
```

## Installation

Voir le guide détaillé dans [INSTALL.md](INSTALL.md).

Démarrage rapide :

```bash
composer install
cp .env.example .env
php artisan key:generate
# configurer la base de données et Stripe dans .env
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
```

Environnement de développement (serveur + queue + logs + Vite en parallèle) :

```bash
composer dev
```

## Tests

```bash
composer test
```

## Sécurité

- HTTPS obligatoire en production, en-têtes de sécurité via `public/.htaccess`
- Protection CSRF, protection brute-force, honeypot/CAPTCHA anti-spam
- Limitation d'inscriptions par IP, verrouillage transactionnel des inscriptions concurrentes
- Uploads validés (MIME, taille), requêtes préparées via Eloquent
- Journalisation des actions d'administration

## Licence

Application développée sur la base du framework Laravel (licence [MIT](https://opensource.org/licenses/MIT)).
