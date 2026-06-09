FROM php:8.3-fpm-alpine

# Extensions système nécessaires
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    icu-dev \
    oniguruma-dev \
    nodejs \
    npm

# Extensions PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    gd \
    mbstring \
    exif \
    pcntl \
    bcmath \
    intl \
    opcache

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Redis PHP extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Config PHP optimisée
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html

USER www-data
