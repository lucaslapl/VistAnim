# AGENTS.md — VistAnim (Nature_Anim-laravel)

> Ce document fournit le contexte du projet aux agents d'IA et aux nouveaux développeurs.
> **Il est conçu pour être mis à jour** : lors de l'ajout d'une fonctionnalité, d'un modèle, d'un service ou d'une commande, pensez à mettre à jour la section correspondante.

---

## 1. Présentation du projet

**VistAnim** est une plateforme de gestion d'animations nature pour un Atlas de la Biodiversité Communale (ABC). Elle permet à une structure (association, collectivité) de :

- Publier des animations nature et gérer les inscriptions du public (gratuites ou payantes via **Stripe**)
- Administrer le tout depuis un back-office avec gestion des rôles (`admin`, `organisateur`)
- Envoyer automatiquement des emails de confirmation, rappels J-1, annulations, récupération de ticket

- **Langue du projet : français** — nommage des routes, vues, méthodes de contrôleurs et contenu en français. Conservez cette convention.
- **Nom de code / dépôt** : `Nature_Anim-laravel` — nom produit : `VistAnim`.

---

## 2. Stack technique

| Domaine | Technologie |
|---|---|
| Framework | Laravel 13 (structure Laravel 11+ : middleware et exceptions dans `bootstrap/app.php`, pas de `Kernel.php`) |
| PHP | 8.3+ |
| Base de données | MySQL / MariaDB (driver `database` pour queue, cache, sessions) |
| Paiement | Stripe (`stripe/stripe-php` v20) |
| Frontend | Vite 8, Tailwind CSS, Alpine.js, Sass |
| Emails | Mailables asynchrones (`ShouldQueue`) — nécessite un worker de queue |
| Qualité | Pint (style), PHPUnit (tests) |

---

## 3. Commandes de référence

```bash
# Installation / mise à jour
composer install
php artisan migrate --seed        # DemoSeeder = données de démonstration
php artisan storage:link
npm install && npm run build      # build production des assets
npm run dev                       # Vite en mode dev

# Développement (serveur + queue + logs + Vite en parallèle)
composer dev

# Tests
composer test                     # php artisan config:clear && php artisan test

# Style
./vendor/bin/pint                # formatage PHP

# Rappels J-1 (planifié toutes les 30 min via routes/console.php)
php artisan reminders:send
```

**Environnement local : WAMP sous Windows** (`C:\wamp64`). Chemins Windows en ligne de commande ; les scripts Composer (`composer dev`) utilisent `concurrently` et fonctionnent depuis la racine du projet.

---

## 4. Architecture

```
app/
├── Console/Commands/SendReminders.php   # reminders:send (rappels J-1, fenêtre 23h45–24h15)
├── Http/
│   ├── Controllers/
│   │   ├── PublicEventController.php        # accueil, agenda, fiche animation
│   │   ├── PublicRegistrationController.php # formulaire, confirmation, gestion par token, ticket
│   │   ├── PaiementController.php           # paiement Stripe (init + vérification)
│   │   ├── StripeWebhookController.php      # webhook Stripe
│   │   ├── AuthController.php               # login/logout admin (auth CUSTOM, pas Breeze)
│   │   ├── AdminDashboardController.php     # tableau de bord
│   │   ├── AdminEventController.php        # CRUD + duplication + brouillons d'événements
│   │   ├── AdminRegistrationController.php # gestion des inscriptions
│   │   ├── AdminUserController.php         # structures/utilisateurs (admin uniquement)
│   │   └── DraftController.php             # suppression des brouillons
│   └── Middleware/CheckRole.php            # alias 'role' → abort(403) si rôle absent
├── Mail/                                 # 9 Mailables (confirmation, rappels, annulations, ticket…)
├── Models/                               # AdminLog, Category, Event, EventDraft, Registration, User
└── Services/
    ├── StripeService.php                 # Toute la logique Stripe passe par ici
    └── MailService.php                   # Centralise l'envoi des Mailables

config/
├── org.php            # Identité de la structure : nom, tagline, contacts, hébergeur (vars APP_ORG_*)
└── stripe.php         # Clés Stripe

resources/views/
├── public/    # Pages publiques (accueil, agenda, détail, inscription, paiement, légales…)
├── admin/     # Back-office (dashboard, événements, structures, inscrits, login)
├── emails/    # Templates d'emails
├── layouts/ includes/ errors/

routes/
├── web.php      # Routes publiques, admin (préfixe /admin, name admin.*), webhook Stripe
└── console.php  # Schedule : reminders:send everyThirtyMinutes()->withoutOverlapping()
```

### Modèles et relations clés

- `Event` — `belongsTo(User, organizer_id)`, `hasMany(Registration)`, `belongsToMany(Category, event_categories)` ; scopes `future`/`past` ; champs notables : `is_paid`, `price_amount`, `reminder_sent`
- `Registration` — inscription d'un participant, avec **token unique** servant de lien magique (gestion/annulation de réservation)
- `EventDraft` — système de brouillons pour les événements en préparation
- `AdminLog` — journal d'audit des actions d'administration
- `User` — champ `role` (`admin` | `organisateur`)

---

## 5. Conventions et points d'attention

### Authentification et rôles
- Auth **custom** (`AuthController`), pas Breeze (Breeze n'est présent qu'en dev pour scaffolding initial).
- Contrôle d'accès via middleware `role:` (`CheckRole`) : `role:admin,organisateur` sur le groupe admin, `role:admin` imbriqué pour les structures. Les routes de modification/suppression utilisent des **POST** (pas de DELETE).

### Stripe
- Toute logique Stripe doit passer par `StripeService`.
- Le webhook `/stripe/webhook` est **exclut de la vérification CSRF** dans `bootstrap/app.php` — ne pas ajouter d'autre exception CSRF sans raison valable.

### Emails
- Tous les Mailables sont `ShouldQueue` → un worker de queue doit tourner (`composer dev` le lance). L'envoi passe par `MailService`.

### Frontend
- Tailwind + Alpine.js (pas de Vue/React côté app). Assets compilés avec Vite (`npm run build`).
- Google Analytics : activé via `GOOGLE_ANALYTICS_ID` dans `.env`.

### Style de code
- Pint (convention Laravel). Indentation : **4 espaces**, fins de ligne LF, UTF-8 (voir `.editorconfig`).
- Français pour le nommage métier (méthodes `creer`, `modifier`, `supprimer`, vues `creer-evenement.blade.php`…). Les classes/namespace restent en anglais standard Laravel.

### Base de données
- Migrations dans `database/migrations/`, seeders dans `database/seeders/` (`DemoSeeder` pour la démo).
- Créer une nouvelle migration plutôt que modifier une migration existante déjà appliquée.

### Sécurité
- `.env` n'est pas versionné. Les secrets Stripe (`STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`) et SMTP sont dans `.env`.
- Les tokens d'inscription sont à usage unique — ne jamais les exposer dans les listes d'inscriptions.
- Journaliser les actions d'administration sensibles via `AdminLog`.

---

## 6. Variables d'environnement notables (`.env`)

Outre la configuration Laravel standard :

| Variable | Rôle |
|---|---|
| `APP_ORG_NAME`, `APP_TAGLINE`, `APP_ORG_DESC`, `APP_ORG_ADDRESS`, `APP_ORG_EMAIL`, `APP_ORG_PHONE`, `APP_ORG_DIRECTOR` | Identité de la structure (lus via `config/org.php`) |
| `APP_HOST_NAME`, `APP_HOST_ADDRESS`, `APP_LOGO_TEXT` | Mentions légales / hébergeur / logo |
| `STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY`, `STRIPE_WEBHOOK_SECRET` | Paiement Stripe |
| `GOOGLE_ANALYTICS_ID` | Google Analytics |

---

## 7. Documentation associée

- `README.md` — présentation générale, stack, démarrage rapide
- `INSTALL.md` — guide d'installation détaillé (prérequis, extensions PHP, Apache, déploiement)

---

## 8. Historique des mises à jour de ce fichier

- 2026-10-01 : création initiale du fichier AGENTS.md.
