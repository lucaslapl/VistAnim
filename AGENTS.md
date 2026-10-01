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
| PHP | 8.4 (le vendor exige ≥ 8.4.1 ; image Docker et production Plesk sur 8.4) |
| Base de données | MySQL 8 (driver `database` pour queue, cache, sessions) |
| Paiement | Stripe (`stripe/stripe-php` v20) |
| Frontend | Vite 8, Tailwind CSS, Alpine.js, Sass |
| Emails | Mailables asynchrones (`ShouldQueue`) — nécessite un worker de queue |
| Qualité | Pint (style), PHPUnit 12 (tests feature : inscription, paiement/webhook, rôles, rappels) |
| Environnement de dev | **Docker uniquement** (voir § 3) — WAMP abandonné |

---

## 3. Environnement et commandes de référence

### Règle environnement (importante)

**L'environnement de développement et de test est Docker exclusivement** (`docker compose up -d`).
WAMP est abandonné : ne pas utiliser le PHP de l'hôte (`C:\wamp64\bin\php\...`) pour
artisan, composer, npm ou les tests. Toute commande PHP/Node se lance dans le conteneur `app` :

```bash
docker compose exec app <commande>
```

Le site est servi sur `http://localhost:8080` (nginx → php-fpm → MySQL 8).
Le `bootstrap/cache` du conteneur est **isolé** de l'hôte (volume anonyme) :
les caches de routes/config générés hors conteneur contiennent des chemins
absolus invalides sous Linux — ne jamais partager ce répertoire entre environnements.

### Commandes usuelles (dans le conteneur)

```bash
docker compose up -d                     # démarrage de la stack
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --class=DemoSeeder   # données de démo

# Tests — LES DEUX suites doivent passer avant toute fusion :
docker compose exec app php artisan test                          # rapide (SQLite :memory:)
docker compose exec app vendor/bin/phpunit -c phpunit.mysql.xml  # réaliste (MySQL 8 : verrous, FK, cascade)

# Style
docker compose exec app vendor/bin/pint

# Assets front (Node inclus dans l'image)
docker compose exec app npm install
docker compose exec app npm run build     # build production des assets

# Queue / logs (développement)
docker compose exec app php artisan queue:work --stop-when-empty
docker compose logs -f app
```

Autres points :
- **CI GitHub Actions** (`.github/workflows/ci.yml`) : Pint + les deux suites de tests
  à chaque push sur `main` et sur chaque pull request.
- `composer dev` (serveur + queue + logs + Vite en parallèle) n'est utilisable que si l'on
  travaille hors Docker ; en Docker, lancer plutôt les commandes ci-dessus.
- `php artisan reminders:send` (rappels J-1) est planifié via `routes/console.php`
  (toutes les 30 min, `withoutOverlapping`).
- Fins de ligne **LF** obligatoires (`.gitattributes` : `* text=auto eol=lf`).

---

## 4. Architecture

```
app/
├── Console/Commands/SendReminders.php   # reminders:send (récap organisateur 23h45–24h15 + rappels participants < 24 h)
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
├── Mail/                                 # 10 Mailables (confirmation, rappels, annulations, suppression d'événement, ticket…)
├── Models/                               # AdminLog, Category, Event, EventDraft, Registration, User
├── Policies/EventPolicy.php              # manage(User, Event) : admin = tous, organisateur = les siens
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

- `Event` — `belongsTo(User, organizer_id)`, `hasMany(Registration)`, `belongsToMany(Category, event_categories)` ; scopes `future`/`past` ; champs notables : `is_paid`, `price_amount`, `reminder_sent`. **SoftDeletes** : la suppression notifie les inscrits, les rembourse, puis conserve l'historique.
- `Registration` — inscription d'un participant, avec **token unique** servant de lien magique (gestion/annulation de réservation). **SoftDeletes** : une annulation conserve l'enregistrement (et l'historique de paiement `payment_intent_id`), les places sont libérées automatiquement via le scope global. Statuts `payment_status` : `pending` → `paid` | `expired` (session Stripe expirée, places libérées) | `refunded`. Le rappel J-1 est suivi par inscription (`reminder_sent`), ce qui couvre les inscriptions tardives.
- `EventDraft` — système de brouillons pour les événements en préparation
- `AdminLog` — journal d'audit des actions d'administration
- `User` — champ `role` (`admin` | `organisateur`)

---

## 5. Conventions et points d'attention

### Authentification et rôles
- Auth **custom** (`AuthController`), pas Breeze (Breeze n'est présent qu'en dev pour scaffolding initial).
- Contrôle d'accès via middleware `role:` (`CheckRole`) : `role:admin,organisateur` sur le groupe admin, `role:admin` imbriqué pour les structures. Les routes de modification/suppression utilisent des **POST** (pas de DELETE).
- Autorisation par ressource : **`$this->authorize('manage', $event)`** (Gate → `EventPolicy`),
  jamais de `abort(403)` manuel dans un contrôleur. Toute nouvelle ressource protégée obtient
  sa Policy.
- Une inscription doit appartenir à l'événement de l'URL (`Registration::where('event_id', ...)`),
  sinon 404 — empêche la manipulation d'inscriptions étrangères par URL croisée.

### Stripe
- Toute logique Stripe doit passer par `StripeService`.
- Le webhook `/stripe/webhook` est **exclut de la vérification CSRF** dans `bootstrap/app.php` — ne pas ajouter d'autre exception CSRF sans raison valable.

### Emails
- Tous les Mailables sont `ShouldQueue` → un worker de queue doit tourner (`composer dev` le lance). L'envoi passe par `MailService`.
- Un Mailable dont les modèles peuvent disparaître avant le passage de la queue (ex. suppression d'événement) ne doit sérialiser que des **scalaires** (`EventDeletionNotification`).

### Frontend
- Tailwind + Alpine.js (pas de Vue/React côté app). Assets compilés avec Vite (`npm run build`).
- Google Analytics : activé via `GOOGLE_ANALYTICS_ID` dans `.env`.

### Git
- **Conventions de commit : Conventional Commits 1.0.0**
  (référence : https://www.conventionalcommits.org —
  cheat-sheet : https://gist.github.com/qoomon/5dfcdf8eec66a051ecd85625518cfd13).
- Format de la première ligne : `<type>[<scope> facultatif]: <description>`
  - Types autorisés : `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`.
  - Scope facultatif entre parenthèses (ex. `fix(paiement):`) ; pas de majuscule ni de point final.
  - Description à l'**impératif, en français**, minuscule, ≤ 72 caractères au total.
  - Breaking change : `!` après le type/scope (`feat!:`) ou pied `BREAKING CHANGE: ...`.
- Corps et pieds facultatifs : pliés à 72 caractères, expliquant le **pourquoi** du changement.
  Pieds normalisés : `BREAKING CHANGE`, `Closes #123`, `Refs #45`, `Co-authored-by: ...`.
- Versionnement implicite (semver) : `feat` → mineure, `fix` → corrective, `BREAKING CHANGE` → majeure.
- Exemples :
  - `feat: ajoute la duplication d'événements`
  - `fix(paiement): empêche la falsification de new_nb via l'URL`
  - `docs: documente l'environnement Docker exclusif`
- Les agents d'IA **rédigent les messages de commit** (et peuvent créer les commits locaux)
  en respectant ces conventions.
- **Seul l'utilisateur peut pousser** : aucun agent ne doit exécuter `git push`
  (ni vers `origin` ni vers tout autre remote), même sur demande implicite.
  Préparer le commit et laisser l'utilisateur pousser.

### Style de code
- Pint (convention Laravel). Indentation : **4 espaces**, fins de ligne LF, UTF-8 (voir `.editorconfig`).
- Français pour le nommage métier (méthodes `creer`, `modifier`, `supprimer`, vues `creer-evenement.blade.php`…). Les classes/namespace restent en anglais standard Laravel.

### Base de données
- Migrations dans `database/migrations/`, seeders dans `database/seeders/` (`DemoSeeder` pour la démo).
- Créer une nouvelle migration plutôt que modifier une migration existante déjà appliquée.
- Capacité d'un événement : verrou pessimiste (`lockForUpdate`) en transaction — toute
  modification de la logique de places doit rester dans une transaction et être couverte
  par `RegistrationFlowTest` / `ReservationManagementTest`.

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
- `INSTALL.md` — guide d'installation détaillé (prérequis, extensions PHP, section Docker)
- `DEPLOY.md` — mise en production sur mutualisé PulseHeberg/Plesk (docroot, crons, Stripe, checklist)

---

## 8. Historique des mises à jour de ce fichier

- 2026-10-01 : création initiale du fichier AGENTS.md.
- 2026-10-01 : passage en environnement Docker exclusif (abandon de WAMP), suites de tests
  SQLite + MySQL, SoftDeletes sur `Registration`, référence à DEPLOY.md.
- 2026-10-01 : règle Git — les agents rédigent les messages de commit, seul l'utilisateur pousse.
- 2026-10-01 : adoption des Conventional Commits 1.0.0 (types, scope, breaking change, pieds).
- 2026-10-01 : P1 sécurité — EventPolicy (`authorize('manage')`), token de ticket non divulgué,
  inscription bloquée aux événements passés, capacité/durcissement de update, suppression de getClientIp.
- 2026-10-01 : P2/P3 — suppression d'événement notifiée et remboursée (SoftDeletes Event),
  rappels par inscription, doublon sous verrou, retrait de laravel/breeze, mise à jour
  des dépendances vulnérables (composer audit à 0), CI GitHub Actions, Pint global.
