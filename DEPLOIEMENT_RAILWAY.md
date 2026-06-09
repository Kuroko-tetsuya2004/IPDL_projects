# 🚂 Guide de déploiement — Railway

## Présentation

[Railway](https://railway.app) est une plateforme cloud qui permet de déployer des applications conteneurisées avec PostgreSQL et Redis intégrés. Ce guide couvre le déploiement complet du portail UMMISCO sur Railway.

---

## Prérequis

- Un compte [Railway](https://railway.app) (plan gratuit ou payant)
- Le code source du projet dans un dépôt **GitHub** ou **GitLab**
- Un serveur Keycloak accessible publiquement (ou utiliser le mode Mock pour les tests)

---

## Architecture sur Railway

```
┌─────────────────────────────────────────────────────┐
│                    Railway Project                   │
│                                                     │
│  ┌─────────────┐  ┌──────────┐  ┌──────────────┐  │
│  │  Laravel App │  │ PostgreSQL│  │    Redis     │  │
│  │  (PHP-FPM + │  │    16     │  │      7       │  │
│  │   Nginx)    │  │           │  │              │  │
│  └──────┬──────┘  └─────┬────┘  └──────┬───────┘  │
│         │               │              │           │
│         └───────────────┴──────────────┘           │
│                                                     │
│  ┌─────────────┐  ┌──────────────┐                 │
│  │ Queue Worker │  │  Keycloak   │ (externe)       │
│  │  (Horizon)  │  │             │                  │
│  └─────────────┘  └──────────────┘                 │
└─────────────────────────────────────────────────────┘
```

---

## Étape 1 — Créer le fichier Dockerfile de production

Créez un fichier `Dockerfile` à la racine du projet (pas dans `docker/php/`) :

```dockerfile
# Dockerfile — Production Railway
FROM php:8.3-fpm-alpine AS base

# Extensions système
RUN apk add --no-cache \
    nginx git curl libpng-dev libzip-dev zip unzip \
    postgresql-dev icu-dev oniguruma-dev supervisor

# Extensions PHP
RUN docker-php-ext-install \
    pdo pdo_pgsql pgsql zip gd mbstring exif \
    pcntl bcmath intl opcache

# Extension Redis
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier le code source
COPY app/ .

# Installer les dépendances (sans dev)
RUN composer install \
    --no-dev --no-interaction --prefer-dist \
    --optimize-autoloader --ignore-platform-reqs

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configuration Nginx
COPY docker/nginx/conf.d/prod.conf /etc/nginx/http.d/default.conf

# Configuration Supervisor (Nginx + PHP-FPM)
COPY docker/railway/supervisord.conf /etc/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
```

---

## Étape 2 — Créer la configuration Supervisor

Créez le fichier `docker/railway/supervisord.conf` :

```ini
[supervisord]
nodaemon=true
logfile=/dev/stdout
logfile_maxbytes=0

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:php-fpm]
command=php-fpm --nodaemonize
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
```

---

## Étape 3 — Fichier de configuration Nginx pour Railway

Créez le fichier `docker/nginx/conf.d/prod.conf` pour écouter sur le port `8080` (port requis par Railway) :

```nginx
server {
    listen 8080;
    server_name _;
    root /var/www/html/public;
    index index.php;

    charset utf-8;
    client_max_body_size 100M;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Étape 4 — Créer le projet sur Railway

### a) Depuis le dashboard Railway

1. Allez sur [railway.app/dashboard](https://railway.app/dashboard)
2. Cliquez sur **« New Project »**
3. Sélectionnez **« Deploy from GitHub Repo »**
4. Autorisez Railway à accéder à votre dépôt
5. Sélectionnez le dépôt du portail UMMISCO

### b) Ajouter PostgreSQL

1. Dans votre projet Railway, cliquez **« + New »** → **« Database »** → **« PostgreSQL »**
2. Railway provisionne automatiquement une instance PostgreSQL 16
3. Notez les variables de connexion (elles seront utilisées à l'étape 6)

### c) Ajouter Redis

1. Cliquez **« + New »** → **« Database »** → **« Redis »**
2. Railway provisionne une instance Redis 7

---

## Étape 5 — Importer le schéma SQL

Connectez-vous à la base PostgreSQL Railway et importez le schéma :

```bash
# Récupérer l'URL de connexion depuis Railway Dashboard → PostgreSQL → Connect
# Format : postgresql://user:password@host:port/database

psql "postgresql://USER:PASSWORD@HOST:PORT/DATABASE" < ummisco_database.sql
```

Ou depuis l'interface Railway :
1. Allez dans le service **PostgreSQL** → **Data** → **Query**
2. Collez le contenu de `ummisco_database.sql`
3. Exécutez

---

## Étape 6 — Configurer les variables d'environnement

Dans Railway, allez dans le service **Laravel App** → **Variables** et ajoutez :

### Variables obligatoires

| Variable | Valeur | Source |
|----------|--------|--------|
| `APP_NAME` | `Portail UMMISCO` | — |
| `APP_ENV` | `production` | — |
| `APP_DEBUG` | `false` | — |
| `APP_KEY` | `base64:...` | Générer avec `php artisan key:generate --show` |
| `APP_URL` | `https://votre-domaine.railway.app` | URL Railway |
| `APP_TIMEZONE` | `Africa/Dakar` | — |
| `APP_LOCALE` | `fr` | — |

### Base de données (référencer les variables Railway)

| Variable | Valeur |
|----------|--------|
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `${{Postgres.PGHOST}}` |
| `DB_PORT` | `${{Postgres.PGPORT}}` |
| `DB_DATABASE` | `${{Postgres.PGDATABASE}}` |
| `DB_USERNAME` | `${{Postgres.PGUSER}}` |
| `DB_PASSWORD` | `${{Postgres.PGPASSWORD}}` |

### Redis

| Variable | Valeur |
|----------|--------|
| `REDIS_HOST` | `${{Redis.REDIS_HOST}}` |
| `REDIS_PORT` | `${{Redis.REDIS_PORT}}` |
| `REDIS_PASSWORD` | `${{Redis.REDIS_PASSWORD}}` |

### Cache et sessions

| Variable | Valeur |
|----------|--------|
| `CACHE_STORE` | `redis` |
| `SESSION_DRIVER` | `redis` |
| `QUEUE_CONNECTION` | `redis` |

### Keycloak

| Variable | Valeur |
|----------|--------|
| `KEYCLOAK_BASE_URL` | `https://votre-keycloak.example.com` |
| `KEYCLOAK_REALM` | `ummisco` |
| `KEYCLOAK_CLIENT_ID` | `laravel-app` |
| `KEYCLOAK_CLIENT_SECRET` | `votre-secret` |
| `KEYCLOAK_REDIRECT_URI` | `https://votre-domaine.railway.app/auth/callback` |
| `KEYCLOAK_MOCK` | `false` |

### Autres

| Variable | Valeur |
|----------|--------|
| `LOG_CHANNEL` | `stderr` |
| `LOG_LEVEL` | `warning` |
| `SESSION_SECURE_COOKIE` | `true` |
| `TELESCOPE_ENABLED` | `false` |

---

## Étape 7 — Configurer le déploiement automatique

Railway détecte automatiquement le `Dockerfile` à la racine. Vérifiez dans **Settings** :

- **Builder** : `Dockerfile`
- **Dockerfile Path** : `./Dockerfile`
- **Watch Paths** : `app/**`, `docker/**`, `Dockerfile`

### Script de démarrage

Ajoutez un script de post-déploiement dans Railway :

**Settings → Deploy → Start Command** (laisser vide si CMD est dans Dockerfile)

Pour exécuter les migrations et optimisations au déploiement, créez `docker/railway/entrypoint.sh` :

```bash
#!/bin/sh
set -e

echo "🚀 Démarrage du portail UMMISCO..."

# Optimisation Laravel pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Caches optimisés"

# Lancer Supervisor (Nginx + PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisord.conf
```

Mettez à jour le `Dockerfile` :
```dockerfile
COPY docker/railway/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
```

---

## Étape 8 — Déployer le Queue Worker

Pour le traitement des files d'attente (notifications, emails), créez un **second service** dans Railway :

1. Cliquez **« + New »** → **« GitHub Repo »** → même dépôt
2. Dans **Settings** :
   - **Start Command** : `php artisan horizon`
   - Utilisez les mêmes variables d'environnement
3. Ce service n'a pas besoin de port exposé

---

## Étape 9 — Domaine personnalisé (optionnel)

1. Dans le service Laravel, allez dans **Settings** → **Networking**
2. Cliquez **« Generate Domain »** → vous obtenez `votre-app.up.railway.app`
3. Pour un domaine personnalisé :
   - Cliquez **« Custom Domain »**
   - Entrez : `portail.ummisco.ucad.sn`
   - Configurez un enregistrement CNAME dans votre DNS :
     ```
     portail.ummisco.ucad.sn → votre-app.up.railway.app
     ```

---

## Étape 10 — Vérification du déploiement

### Checklist de déploiement

- [ ] L'application est accessible via l'URL Railway
- [ ] La page d'accueil s'affiche correctement
- [ ] La connexion Keycloak fonctionne (ou mode Mock si test)
- [ ] Les publications sont chargées depuis PostgreSQL
- [ ] Les axes thématiques s'affichent
- [ ] Le workflow de validation fonctionne
- [ ] Les logs sont visibles dans Railway → **Observability**
- [ ] `KEYCLOAK_MOCK=false` en production
- [ ] `APP_DEBUG=false` en production

### Monitoring

Railway fournit des métriques intégrées :
- **CPU / RAM** : Observability → Metrics
- **Logs** : Observability → Logs (temps réel)
- **Redéploiement** : chaque push sur la branche configurée déclenche un redéploiement

---

## Coûts estimés (Railway)

| Service | Plan gratuit | Plan Hobby ($5/mois) | Plan Pro ($20/mois) |
|---------|-------------|----------------------|---------------------|
| App Laravel | 500h/mois | Illimité | Illimité |
| PostgreSQL | 1 Go | 10 Go | 50 Go |
| Redis | 256 Mo | 1 Go | 5 Go |
| **Total** | **Gratuit** | **~$10/mois** | **~$30/mois** |

> Le plan Hobby est suffisant pour un portail institutionnel à trafic modéré.
