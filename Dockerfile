# Dockerfile — Production

# ── STAGE 1 : Build des assets front-end (Node) ──────────────────────────────
FROM node:20-alpine AS node_builder

WORKDIR /app

# Copier package.json en premier pour profiter du cache Docker
COPY app/package.json ./
RUN npm install

# Copier tout le code source
COPY app/ .

# Builder les assets Vite → génère public/build/manifest.json
RUN npm run build

# ── STAGE 2 : Image PHP de production ────────────────────────────────────────
FROM php:8.3-fpm-alpine

# Extensions système requises
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

# Copier le code source PHP
COPY app/ .

# ✅ Récupérer les assets buildés depuis le stage Node
COPY --from=node_builder /app/public/build ./public/build

# Copier le schéma SQL
COPY ummisco_database.sql /var/www/html/ummisco_database.sql

# Créer l'arborescence complète de stockage Laravel et définir les permissions
RUN mkdir -p /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Installer les dépendances PHP (sans dev)
RUN composer install \
    --no-dev --no-interaction --prefer-dist \
    --optimize-autoloader --ignore-platform-reqs

# Configuration Nginx
COPY docker/nginx/conf.d/prod.conf /etc/nginx/http.d/default.conf

# Configuration Supervisor (Nginx + PHP-FPM)
COPY docker/railway/supervisord.conf /etc/supervisord.conf

# Script d'entrypoint de production
COPY docker/railway/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

CMD ["/entrypoint.sh"]