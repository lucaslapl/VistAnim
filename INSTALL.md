# Guide d'installation — VistAnim (Laravel)

> Plateforme de gestion d'animations nature (Atlas de la Biodiversité Communale)

---

## 1. Prérequis techniques

| Technologie | Version minimale |
|---|---|
| PHP | 8.3 ou supérieure |
| Composer | 2.x |
| Node.js / NPM | 18.x / 9.x |
| MySQL ou MariaDB | 5.7 / 10.3 ou supérieure |
| Apache (ou Nginx) | Dernière version stable |

> Basé sur **Laravel 13**. Paiement via **Stripe**, assets compilés avec **Vite** (Tailwind CSS, Alpine.js, Sass).

### Extensions PHP

- `pdo` + `pdo_mysql`
- `mbstring`
- `openssl`
- `json`
- `fileinfo`
- `zip`
- `curl`
- `bcmath`
- `xml`
- `tokenizer`

### Modules Apache

- `mod_rewrite`
- `mod_headers`
- `mod_ssl` (HTTPS)

---

## 2. Installation

```bash
# 1. Cloner le dépôt
git clone <url-du-depot> /chemin/vers/site
cd /chemin/vers/site

# 2. Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# 3. Installer les dépendances Node et compiler les assets
npm install && npm run build

# 4. Configuration de l'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données dans .env
#    DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 6. Migrations et seed (données de démonstration)
php artisan migrate --seed

# 7. Créer le lien de stockage
php artisan storage:link

# 8. Optimisation (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Configuration utilisateur

### Application — `.env`

```
APP_NAME="ABC de Ma Ville"
APP_TAGLINE="..."
APP_ORG_NAME="..."
APP_ORG_DESC="..."
APP_ORG_ADDRESS="..."
APP_ORG_EMAIL="..."
APP_ORG_PHONE="..."
APP_ORG_DIRECTOR="..."
APP_HOST_NAME="..."
APP_HOST_ADDRESS="..."
APP_LOGO_TEXT="🌿"
```

### Paiement Stripe

```
STRIPE_PUBLISHABLE_KEY=pk_live_...
STRIPE_SECRET_KEY=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

> L'URL du webhook Stripe à configurer côté tableau de bord Stripe est : `https://votre-domaine/stripe/webhook`

### Envoi d'emails

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 4. File d'attente (queue)

Les emails (9 Mailables) sont envoyés de manière asynchrone via la file d'attente (`ShouldQueue`). Le projet utilise le driver `database` par défaut. La table `jobs` est déjà créée par les migrations, aucune commande supplémentaire n'est nécessaire.

```bash
# Dans .env
QUEUE_CONNECTION=database
```

### Lancer le worker

```bash
# En arrière-plan (production)
php artisan queue:work &

# Ou via Supervisor (recommandé)
```

> En développement, la commande `composer dev` lance automatiquement un worker (`queue:listen`) en parallèle du serveur, des logs et de Vite.

---

## 5. Tâches planifiées (cron)

Ajouter cette ligne au crontab :

```bash
* * * * * /usr/bin/php /chemin/vers/site/artisan schedule:run >> /dev/null 2>&1
```

### Commande de rappel J-1

```bash
php artisan reminders:send
```

Cette commande envoie les rappels aux participants et le récapitulatif à l'organisateur pour chaque animation ayant lieu le lendemain. Elle est programmée automatiquement dans `routes/console.php` toutes les 30 minutes (avec `withoutOverlapping`).

---

## 6. Données de démonstration

```bash
php artisan db:seed --class=DemoSeeder
```

Crée :
- 1 administrateur + 3 organisateurs
- 8 catégories d'événements
- 8 événements avec dates
- 31 inscriptions avec statuts variés

### Comptes de démo

| Rôle | Structure | Email | Mot de passe |
|---|---|---|---|
| Admin | Gestionnaire Municipal | `admin@nature-demo.fr` | `demo1234` |
| Organisateur | Les Amis de la Forêt | `contact@amis-foret.fr` | `demo1234` |
| Organisateur | Nature et Découverte | `contact@nature-decouverte.fr` | `demo1234` |
| Organisateur | Éveil Naturaliste | `contact@eveil-naturaliste.fr` | `demo1234` |

---

## 7. Structure du projet

```
app/
├── Console/Commands/      # SendReminders (reminders:send)
├── Http/Controllers/      # Contrôleurs publics et admin (structure plate)
├── Mail/                  # 9 classes Mailable (ShouldQueue)
├── Models/                # AdminLog, Category, Event, EventDraft, Registration, User
└── Services/              # StripeService, MailService

config/
├── app.php
├── database.php
├── mail.php
├── org.php                # Configuration de la structure
├── services.php           # Services tiers
└── stripe.php             # Clés Stripe

database/
├── migrations/            # Schéma de base de données
├── factories/
└── seeders/               # DemoSeeder

resources/views/
├── admin/                 # Templates d'administration
├── public/                # Pages publiques
├── emails/                # Templates d'emails
├── layouts/ includes/ errors/

routes/
├── web.php                # Routes web (public + admin + webhook Stripe)
└── console.php            # Planification des commandes
```

---

## 8. Sécurité

- **HTTPS obligatoire** en production
- **CSRF tokens** sur tous les formulaires
- **Authentification** — mots de passe hachés (bcrypt)
- **Protection brute-force** — blocage après 5 tentatives échouées
- **Honeypot + CAPTCHA** — anti-spam sur inscriptions publiques
- **Limitation par IP** — 3 inscriptions/heure/événement
- **Audit logging** — toutes les actions admin tracées
- **Uploads sécurisés** — vérification MIME, limite 2 Mo
- **Requêtes préparées** (Eloquent) — pas d'injections SQL
- **Verrouillage transactionnel** — sérialisation des inscriptions concurrentes

### En-têtes HTTP de sécurité (configurés dans `public/.htaccess`)

```
X-Frame-Options: DENY
X-XSS-Protection: 0
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'
```
