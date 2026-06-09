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
