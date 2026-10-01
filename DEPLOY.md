# Guide de déploiement — PulseHeberg (mutualisé, Plesk)

> Ce document décrit la mise en production du site sur un hébergement mutualisé
> PulseHeberg administré via **Plesk**. Docker n'est **pas** disponible sur un
> mutualisé : l'environnement Docker du dépôt sert uniquement au développement
> et aux tests. En production, l'application tourne en PHP natif.

---

## 1. Prérequis

- Un domaine hébergé avec **Plesk Obsidian**, accès SSH activé (Plesk > Web Hosting Access)
- **PHP 8.4** sélectionné pour le domaine (Plesk > PHP ; l'application requiert ≥ 8.4)
- Extension **Git** de Plesk installée (ou accès rsync/SFTP)
- Une **base de données MySQL** créée dans Plesk
- Un compte Stripe (mode live) + un compte email SMTP

## 2. Préparer le paquet en local (une seule fois, puis à chaque mise à jour)

Sur la machine de développement (ou dans le conteneur Docker) :

```bash
# 1. Vérifier que tout est vert avant de partir
docker compose exec app php artisan test
docker compose exec app vendor/bin/phpunit -c phpunit.mysql.xml

# 2. Compiler les assets front (pas de Node.js sur le mutualisé)
npm ci && npm run build

# 3. Dépendances PHP sans les outils de dev
composer install --no-dev --optimize-autoloader
```

> Si Composer n'est pas disponible sur l'hébergement, uploader aussi le
> répertoire `vendor/` généré en local (il est pur PHP, sans extension native).

## 3. Déployer le code

### Option A — Plesk Git (recommandé)

1. Peler **Plesk > Git** pour le domaine, y ajouter le dépôt (GitHub/GitLab).
2. Déployer dans `httpdocs/`.
3. Dans les paramètres du dépôt Git, désactiver le déploiement automatique ou le
   garder sur une branche `production` dédiée.

### Option B — SFTP / rsync

Uploader l'intégralité du projet dans `httpdocs/`, **à l'exclusion** de :
`.git`, `node_modules`, `tests`, `Dockerfile`, `docker-compose.yml`, `docker/`,
`phpunit*.xml`, `.env` local.

## 4. Document root

Le site doit être servi depuis le sous-répertoire `public` :

**Plesk > Hosting Settings > Document root** : `httpdocs/public`
(cocher à la fois HTTP et HTTPS si deux racines sont proposées).

Cela protège `.env`, `composer.json`, `storage/`, etc. qui ne doivent jamais
être accessibles par HTTP. Si la racine ne peut pas être modifiée (offre
limitée), contacter le support PulseHeberg — ne pas déplacer les fichiers.

## 5. Base de données

1. **Plesk > Bases de données** : créer la base + un utilisateur (privilèges complets).
2. Renseigner `DB_*` dans le `.env` de production (voir § 6).
3. Lancer les migrations (via SSH) :

```bash
/opt/plesk/php/8.4/bin/php artisan migrate --force
```

Le chemin du binaire PHP peut varier ; `which php` en SSH le confirme.

## 6. Fichier `.env` de production

Créer `httpdocs/.env` à partir de `.env.example` puis renseigner :

```ini
APP_ENV=production
APP_DEBUG=false            # OBLIGATOIRE : false en production
APP_URL=https://votre-domaine.fr

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xxxxxxxx
DB_USERNAME=xxxxxxxx
DB_PASSWORD=xxxxxxxx

MAIL_MAILER=smtp
MAIL_HOST=mail.votre-domaine.fr     # ou le SMTP fourni par PulseHeberg
MAIL_PORT=587
MAIL_USERNAME=xxxxxxxx
MAIL_PASSWORD=xxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votre-domaine.fr

STRIPE_PUBLISHABLE_KEY=pk_live_...
STRIPE_SECRET_KEY=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...     # voir § 8

QUEUE_CONNECTION=database

# Identité de la structure (visibles sur le site)
APP_TAGLINE=...
APP_ORG_NAME=...
APP_ORG_DESC=...
# etc. (voir INSTALL.md § 3)
```

Puis générer la clé :

```bash
/opt/plesk/php/8.4/bin/php artisan key:generate --force
```

## 7. Tâches planifiées (Plesk > Scheduled Tasks)

Sur un mutualisé, il n'y a ni daemon ni Supervisor : tout passe par **cron**.
Créer les deux tâches suivantes (fréquence : **toutes les minutes**) :

```bash
# 1. Scheduler Laravel (rappels J-1, etc.)
/opt/plesk/php/8.4/bin/php /var/www/vhosts/votre-domaine.fr/httpdocs/artisan schedule:run

# 2. Worker de file d'attente (envoi des 9 types d'emails)
/opt/plesk/php/8.4/bin/php /var/www/vhosts/votre-domaine.fr/httpdocs/artisan queue:work --stop-when-empty --max-time=55
```

> Sans worker de queue, **aucun email ne part** (les Mailables sont tous `ShouldQueue`).
> Si le plan d'hébergement interdit les crons à la minute, passer
> `QUEUE_CONNECTION=sync` dans `.env` en dernier recours (emails envoyés dans
> la requête HTTP, plus lent pour l'utilisateur).

> **Deux options pour le worker — en choisir UNE seule :**
>
> 1. **Laravel Toolkit** (recommandé) : le paquet `plesk/ext-laravel-integration`
>    (épinglé `^8.0` dans composer.json) permet d'activer le toggle
>    *Queues* du Toolkit. Le worker `default` (notre seule file) est alors
>    géré par Plesk — inutile de créer la tâche cron 2.
>    **Prérequis Plesk** (KB officielle) : `open_basedir = none` (PHP Settings),
>    accès SSH `/bin/bash` (Hosting Settings), permission *Scheduler management*
>    sur l'abonnement, et surtout le toggle **Scheduled Tasks** du Toolkit activé —
>    le worker s'exécute via ce mécanisme ; sans lui, les jobs restent dans la
>    table `jobs` avec `attempts = 0`.
> 2. **Cron** : la tâche 2 ci-dessus, sans dépendre du Toolkit.
>
> Dans les deux cas, la tâche 1 (scheduler) reste **obligatoire** : le worker
> ne déclenche pas les tâches planifiées (rappels J-1).

## 8. Webhook Stripe

1. Tableau de bord Stripe > **Developers > Webhooks > Add endpoint**.
2. URL : `https://votre-domaine.fr/stripe/webhook`
3. Événements à sélectionner :
   - `checkout.session.completed`
   - `checkout.session.expired` (libère les places des paniers abandonnés)
   - `charge.refunded`
4. Copier le **signing secret** (`whsec_...`) dans `STRIPE_WEBHOOK_SECRET`.

> Le CSRF est déjà désactivé pour cette route (voir `bootstrap/app.php`),
> et la signature Stripe est vérifiée côté contrôleur.

## 9. HTTPS

- **Plesk > Let's Encrypt** : émettre le certificat, activer la redirection
  permanente 301 vers `https://`.
- Stripe exige du HTTPS pour les webhooks et le Checkout.
- Mettre à jour `APP_URL` en `https://`.

## 10. Optimisation finale

Toujours en SSH, dans `httpdocs/` :

```bash
/opt/plesk/php/8.4/bin/php artisan optimize    # config:cache + route:cache + view:cache
```

Permissions attendues (Plesk fait tourner PHP sous l'utilisateur de l'abonnement,
les droits sont en général corrects d'office) :

```
storage/            → inscriptible (755 suffit)
bootstrap/cache/    → inscriptible
public/assets/images/animations/ → inscriptible (upload des images d'événements)
```

> L'application n'utilise pas `storage:link` : les images uploadées vont
> directement dans `public/assets/images/animations/`.

## 11. Sauvegardes et logs

### Sauvegarde de la base (quotidienne)

Sur un mutualisé, créer une **tâche planifiée Plesk** (fréquence : quotidienne, ex. 3 h du matin) :

```bash
mkdir -p /var/www/vhosts/votre-domaine.fr/private/backups
/opt/plesk/php/8.4/bin/php -r 'exit(0);' # vérifier le binaire PHP, puis :
mysqldump --host=127.0.0.1 --user=<user> --password=<motdepasse> <base> \
  | gzip > /var/www/vhosts/votre-domaine.fr/private/backups/db-$(date +\%F).sql.gz
find /var/www/vhosts/votre-domaine.fr/private/backups -name 'db-*.sql.gz' -mtime +30 -delete
```

> Le répertoire `private/` n'est **pas** accessible par HTTP. Conserver au moins 30 jours.
> Si l'offre inclut les sauvegardes automatiques Plesk, vérifier leur fréquence et
> la rétention dans Plesk > Tools & Settings > Backup Manager — la tâche ci-dessus
> reste utile pour un export hors de l'hébergement.

### Rotation des logs applicatifs

Le `.env` de production utilise `LOG_STACK=daily` : un fichier `laravel-YYYY-MM-DD.log`
par jour, rétention de 14 jours (`LOG_DAILY_DAYS`). Sans cela, `laravel.log` croît
indéfiniment sur un mutualisé sans accès à logrotate.

À surveiller après mise en production : `storage/logs/laravel-*.log` (erreurs
remboursement, queue, webhooks Stripe).

## 12. Checklist de mise en production

- [ ] `docker compose exec app php artisan test` vert en local (sqlite **et** mysql)
- [ ] `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` en https
- [ ] Document root sur `httpdocs/public`
- [ ] `php artisan migrate --force` exécuté
- [ ] Cron scheduler toutes les minutes
- [ ] Worker de queue actif (toggle Queues du Laravel Toolkit **ou** cron `queue:work`)
- [ ] Webhook Stripe configuré + `STRIPE_WEBHOOK_SECRET`
- [ ] `php artisan optimize` exécuté (à refaire après chaque modif de `.env`)
- [ ] `https://votre-domaine.fr/up` renvoie 200
- [ ] Inscription de test → email de confirmation reçu
- [ ] Paiement Stripe de test (mode live avec carte réelle à petit montant, puis remboursement)
- [ ] Connexion admin OK, les mots de passe de démo ne sont **pas** seedés en production
      (ne pas lancer `db:seed --class=DemoSeeder`)

## 13. Procédure de mise à jour

1. Fusionner sur `main`, faire tourner les tests (Docker).
2. `composer install --no-dev --optimize-autoloader` + `npm run build` en local.
3. Déployer via Plesk Git (ou rsync).
4. En SSH :
   ```bash
   /opt/plesk/php/8.4/bin/php artisan down            # maintenance (optionnel)
   /opt/plesk/php/8.4/bin/php artisan migrate --force
   /opt/plesk/php/8.4/bin/php artisan queue:restart  # le worker recharge le nouveau code
   /opt/plesk/php/8.4/bin/php artisan optimize
   /opt/plesk/php/8.4/bin/php artisan up
   ```
5. Contrôler `https://votre-domaine.fr/up` et `storage/logs/laravel.log`.
