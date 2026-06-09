# 🌐 Guide de Déploiement de Production — Portail UMMISCO

Ce guide présente la procédure complète et détaillée pour déployer le Portail Web UMMISCO dans un environnement de production sécurisé, performant et hautement disponible.

---

## 🗺️ Architecture de Production cible

L'architecture est construite autour de conteneurs Docker orchestrés par **Docker Compose** (ou Kubernetes) et sécurisés par un reverse proxy **Nginx** avec certificats **SSL (Let's Encrypt)**.

```
                     ┌──────────────────────────────┐
                     │      Utilisateurs (HTTPS)    │
                     └──────────────┬───────────────┘
                                    │
                                    ▼
                     ┌──────────────────────────────┐
                     │    Reverse Proxy Nginx       │ (Port 80/443 + SSL)
                     └─┬────────────┬────────────┬──┘
                       │            │            │
  ┌────────────────────▼───┐  ┌─────▼──────┐  ┌──▼───────────────────┐
  │  Service Keycloak     │  │ MinIO API  │  │ Application Laravel  │ (php-fpm + Node.js)
  │  (Auth OIDC - 8180)    │  │  (9000)    │  │ (Port 8080/9000)     │
  └────────────────────────┘  └────────────┘  └──────────┬───────────┘
                                                         │ (Interne)
                                      ┌──────────────────┼──────────────────┐
                                      ▼                  ▼                  ▼
                              ┌──────────────┐   ┌──────────────┐   ┌──────────────┐
                              │ PostgreSQL 16│   │   Redis 7    │   │ Queue Worker │
                              │ (Base - 5432)│   │ (Cache/Queue)│   │  (Horizon)   │
                              └──────────────┘   └──────────────┘   └──────────────┘
```

---

## 📋 Prérequis matériels & logiciels

* **Serveur (VPS / Dédié)** : Ubuntu 22.04 LTS (minimum 2 vCPUs, 4 Go RAM).
* **Nom de Domaine** : Enregistrements DNS (A) configurés pour vos sous-domaines :
  - `portail.ummisco.sn` (Application principale)
  - `auth.portail.ummisco.sn` (Keycloak)
  - `storage.portail.ummisco.sn` (API MinIO)
  - `console.storage.portail.ummisco.sn` (Console MinIO)
* **Serveur SMTP** : Pour l'envoi de mails et de notifications.
* **Outils système** : `docker`, `docker-compose-plugin`, `certbot`, `git`.

---

## 🛠️ ÉTAPE 1 — Préparation du Serveur de Production

### 1. Mise à jour du système & dépendances
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y git curl zip unzip certbot python3-certbot-nginx
```

### 2. Installation de Docker & Docker Compose
```bash
# Installation de Docker Engine
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Ajout de l'utilisateur actuel au groupe docker
sudo usermod -aG docker $USER
newgrp docker
```

---

## 📦 ÉTAPE 2 — Fichier `docker-compose.prod.yml`

Créez le fichier de déploiement de production suivant à la racine du projet :

```yaml
version: '3.8'

services:
  # ── BASE DE DONNÉES POSTGRESQL ─────────────────────────────────────────────
  postgres:
    image: postgres:16-alpine
    container_name: ummisco_postgres_prod
    restart: always
    environment:
      POSTGRES_DB: ${DB_DATABASE}
      POSTGRES_USER: ${DB_USERNAME}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_prod_data:/var/lib/postgresql/data
    networks:
      - backend_net

  # ── CACHE & FILES D'ATTENTE REDIS ─────────────────────────────────────────
  redis:
    image: redis:7-alpine
    container_name: ummisco_redis_prod
    restart: always
    command: redis-server --requirepass ${REDIS_PASSWORD}
    volumes:
      - redis_prod_data:/data
    networks:
      - backend_net

  # ── STOCKAGE OBJET S3 (MINIO) ──────────────────────────────────────────────
  minio:
    image: minio/minio:latest
    container_name: ummisco_minio_prod
    restart: always
    environment:
      MINIO_ROOT_USER: ${MINIO_ROOT_USER}
      MINIO_ROOT_PASSWORD: ${MINIO_ROOT_PASSWORD}
      MINIO_BROWSER_REDIRECT_URL: https://console.storage.portail.ummisco.sn
    command: server /data --console-address ":9001"
    volumes:
      - minio_prod_data:/data
    networks:
      - backend_net
      - frontend_net

  # ── AUTHENTIFICATION KEYCLOAK ──────────────────────────────────────────────
  keycloak:
    image: quay.io/keycloak/keycloak:24.0
    container_name: ummisco_keycloak_prod
    restart: always
    command: start --optimized
    environment:
      KC_HOSTNAME: auth.portail.ummisco.sn
      KC_PROXY_HEADERS: xforwarded
      KC_HTTP_ENABLED: "true"
      KC_DB: postgres
      KC_DB_URL: jdbc:postgresql://postgres:5432/${DB_DATABASE}
      KC_DB_USERNAME: ${DB_USERNAME}
      KC_DB_PASSWORD: ${DB_PASSWORD}
      KEYCLOAK_ADMIN: ${KEYCLOAK_ADMIN_USER}
      KEYCLOAK_ADMIN_PASSWORD: ${KEYCLOAK_ADMIN_PASSWORD}
    depends_on:
      - postgres
    networks:
      - backend_net
      - frontend_net

  # ── APPLICATION PORTAIL WEB ───────────────────────────────────────────────
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile.prod
    container_name: ummisco_app_prod
    restart: always
    environment:
      APP_ENV: production
      APP_DEBUG: "false"
      APP_KEY: ${APP_KEY}
      APP_URL: https://portail.ummisco.sn
      DB_HOST: postgres
      DB_PORT: 5432
      DB_DATABASE: ${DB_DATABASE}
      DB_USERNAME: ${DB_USERNAME}
      DB_PASSWORD: ${DB_PASSWORD}
      REDIS_HOST: redis
      REDIS_PASSWORD: ${REDIS_PASSWORD}
      KEYCLOAK_BASE_URL: https://auth.portail.ummisco.sn
      KEYCLOAK_REALM: ummisco
      KEYCLOAK_CLIENT_ID: laravel-app
      KEYCLOAK_CLIENT_SECRET: ${KEYCLOAK_CLIENT_SECRET}
      KEYCLOAK_REDIRECT_URI: https://portail.ummisco.sn/auth/callback
      KEYCLOAK_MOCK: "false"
      MINIO_ENDPOINT: https://storage.portail.ummisco.sn
      MINIO_KEY: ${MINIO_ACCESS_KEY}
      MINIO_SECRET: ${MINIO_SECRET_KEY}
      MINIO_BUCKET: ummisco-public
    depends_on:
      - postgres
      - redis
    volumes:
      - app_prod_storage:/var/www/html/storage
    networks:
      - backend_net
      - frontend_net

  # ── SCHEDULER & CRON ───────────────────────────────────────────────────────
  scheduler:
    build:
      context: .
      dockerfile: docker/php/Dockerfile.prod
    container_name: ummisco_scheduler_prod
    restart: always
    command: php artisan schedule:work
    depends_on:
      - app
    networks:
      - backend_net

  # ── QUEUE WORKER (TRAITEMENTS ASYNCHRONES) ────────────────────────────────
  queue_worker:
    build:
      context: .
      dockerfile: docker/php/Dockerfile.prod
    container_name: ummisco_queue_prod
    restart: always
    command: php artisan queue:work --queue=default --sleep=3 --tries=3
    depends_on:
      - app
    networks:
      - backend_net

networks:
  backend_net:
    driver: bridge
  frontend_net:
    driver: bridge

volumes:
  postgres_prod_data:
  redis_prod_data:
  minio_prod_data:
  app_prod_storage:
```

---

## 🐳 ÉTAPE 3 — Le Dockerfile de Production (`docker/php/Dockerfile.prod`)

Créez le fichier de configuration de conteneur PHP optimisé pour la production :

```dockerfile
# /docker/php/Dockerfile.prod
FROM php:8.3-fpm-alpine

# Installation des paquets et extensions requis
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    icu-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev

RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    gd \
    intl \
    bcmath \
    opcache

# Installation de Redis extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Récupération de Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie des fichiers sources de l'application
COPY app/ /var/www/html/

# Installation des dépendances Composer (sans outils de dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions adéquates pour le serveur web
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
```

---

## 🔒 ÉTAPE 4 — Configuration Reverse Proxy Nginx & SSL

Sur votre serveur hôte (hors Docker), configurez Nginx pour gérer le trafic HTTPS et rediriger les requêtes vers les conteneurs correspondants.

### 1. Fichier de Configuration de base (`/etc/nginx/sites-available/portail_ummisco`)

```nginx
# 1. Portail Principal Laravel
server {
    listen 80;
    server_name portail.ummisco.sn;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name portail.ummisco.sn;

    ssl_certificate /etc/letsencrypt/live/portail.ummisco.sn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/portail.ummisco.sn/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    root /var/www/html/public; # Monté sur l'hôte ou proxifié

    location / {
        proxy_pass http://localhost:8080; # Redirection vers le conteneur Nginx/App
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# 2. Serveur d'Authentification (Keycloak)
server {
    listen 80;
    server_name auth.portail.ummisco.sn;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name auth.portail.ummisco.sn;

    ssl_certificate /etc/letsencrypt/live/auth.portail.ummisco.sn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/auth.portail.ummisco.sn/privkey.pem;

    location / {
        proxy_pass http://localhost:8180;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_set_header X-Forwarded-Port 443;
    }
}

# 3. Stockage MinIO API
server {
    listen 80;
    server_name storage.portail.ummisco.sn;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name storage.portail.ummisco.sn;

    ssl_certificate /etc/letsencrypt/live/storage.portail.ummisco.sn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/storage.portail.ummisco.sn/privkey.pem;

    ignore_invalid_headers off;
    client_max_body_size 100M;

    location / {
        proxy_pass http://localhost:9000;
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }
}
```

### 2. Activation des configurations et génération des certificats SSL
```bash
# Activation du site Nginx
sudo ln -s /etc/nginx/sites-available/portail_ummisco /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Obtention automatique des certificats SSL avec Certbot
sudo certbot --nginx -d portail.ummisco.sn -d auth.portail.ummisco.sn -d storage.portail.ummisco.sn
```

---

## 🔐 ÉTAPE 5 — Configuration Securisée Keycloak (Production)

Une fois Keycloak démarré :
1. Accédez à la console d'administration sur `https://auth.portail.ummisco.sn`.
2. Connectez-vous avec vos identifiants admin définis dans le `.env` de production.
3. Créez le Realm `ummisco`.
4. Configurez le Client OIDC `laravel-app` :
   * **Client Authentication** : ✅ `On` (Requis pour obtenir le Client Secret).
   * **Valid Redirect URIs** : `https://portail.ummisco.sn/auth/callback`.
   * **Web Origins** : `https://portail.ummisco.sn`.
   * **Valid Post Logout Redirect URIs** : `https://portail.ummisco.sn`.
5. Créez vos rôles système (`super_admin`, `axe_admin`, `researcher`, `doctoral_student`, `partner`, `visitor`).
6. Récupérez le **Client Secret** depuis l'onglet `Credentials` de votre client pour l'ajouter aux variables d'environnement Laravel.

---

## 🗄️ ÉTAPE 6 — Configuration de Stockage MinIO (Production)

1. Accédez à la console de gestion MinIO (exposée sur le port `9001`).
2. Connectez-vous avec vos identifiants `MINIO_ROOT_USER` et `MINIO_ROOT_PASSWORD`.
3. Allez dans **Buckets** et créez les buckets requis :
   * `ummisco-public` (Pour les documents publics, publications et thèses).
   * `datasets` (Pour les bases de données brutes d'épidémiologie/modélisation).
   * `medias` (Pour les avatars, images d'axes et fichiers annexes).
4. Configurez les permissions d'accès (Access Policy) sur **Public** ou **Custom** pour `ummisco-public` afin de permettre le téléchargement et l'affichage direct des documents scientifiques.
5. Générez une paire de clés d'accès **Access Key / Secret Key** depuis la section **Access Keys** de MinIO pour l'associer à l'application Laravel.

---

## 🌐 ÉTAPE 7 — Variables d'Environnement de Production (`.env`)

Créez un fichier `.env` sécurisé dans le répertoire de l'application Laravel (`/app`) :

```env
APP_NAME="Portail Web UMMISCO"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:A_GENERER_AVEC_PHP_ARTISAN_KEY_GENERATE
APP_URL=https://portail.ummisco.sn
APP_TIMEZONE=Africa/Dakar
APP_LOCALE=fr

# ── LOGS ──────────────────────────────────────────────────────────────────────
LOG_CHANNEL=stderr
LOG_LEVEL=warning

# ── POSTGRESQL DATABASE ───────────────────────────────────────────────────────
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=ummisco_app
DB_USERNAME=ummisco_db_admin
DB_PASSWORD=MOT_DE_PASSE_TRES_SECURISE_A_MODIFIER

# ── REDIS CACHE / SESSIONS / QUEUE ────────────────────────────────────────────
REDIS_HOST=redis
REDIS_PASSWORD=MOT_DE_PASSE_REDIS_SECURISE
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# ── CONFIGURATION KEYCLOAK (PROD) ─────────────────────────────────────────────
KEYCLOAK_BASE_URL=https://auth.portail.ummisco.sn
KEYCLOAK_REALM=ummisco
KEYCLOAK_CLIENT_ID=laravel-app
KEYCLOAK_CLIENT_SECRET=SECRET_OBTENU_A_L_ETAPE_5
KEYCLOAK_REDIRECT_URI=https://portail.ummisco.sn/auth/callback
KEYCLOAK_MOCK=false

# ── STOCKAGE MINIO / S3 DE PRODUCTION ──────────────────────────────────────────
MINIO_ENDPOINT=https://storage.portail.ummisco.sn
MINIO_KEY=VOTRE_ACCESS_KEY_MINIO
MINIO_SECRET=VOTRE_SECRET_KEY_MINIO
MINIO_BUCKET=ummisco-public

# ── SECURE COOKIES & TLS ──────────────────────────────────────────────────────
SESSION_SECURE_COOKIE=true
TELESCOPE_ENABLED=false
```

---

## 🚀 ÉTAPE 8 — Lancement & Optimisations

### 1. Construction et démarrage des conteneurs
```bash
docker compose -f docker-compose.prod.yml up -d --build
```

### 2. Initialisation de la base de données
```bash
# Exécution des migrations de base de données
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Importation du schéma préexistant et données institutionnelles
docker compose -f docker-compose.prod.yml exec -T postgres psql -U ummisco_db_admin -d ummisco_app < ummisco_database.sql
```

### 3. Commandes d'optimisation Laravel
```bash
# Génération de la clé d'application (si non générée)
docker compose -f docker-compose.prod.yml exec app php artisan key:generate

# Mise en cache de la configuration et des routes
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache

# Création du lien symbolique de stockage
docker compose -f docker-compose.prod.yml exec app php artisan storage:link
```

---

## 🛡️ ÉTAPE 9 — Stratégie de Maintenance & Sauvegardes

### 1. Automatisation de sauvegarde PostgreSQL
Créez une tâche Cron quotidienne sur l'hôte :
```bash
# Ouvrir le crontab
crontab -e

# Sauvegarde tous les jours à 2h du matin
0 2 * * * docker exec ummisco_postgres_prod pg_dump -U ummisco_db_admin ummisco_app > /backups/db_backup_$(date +\%F).sql
```

### 2. Surveillance des ressources et des journaux
```bash
# Voir les logs de l'application Laravel
docker logs -f ummisco_app_prod --tail=50

# Voir l'état des conteneurs
docker stats
```
