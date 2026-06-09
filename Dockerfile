# Dockerfile — Production
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

# Copier le code source
COPY app/ .

# Créer les répertoires nécessaires et définir les permissions avant le composer install
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Installer les dépendances (sans dev)
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
