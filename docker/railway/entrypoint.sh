#!/bin/sh
set -e

echo "🚀 Démarrage du portail UMMISCO..."

# S'assurer que les dossiers de stockage et cache existent avec les bonnes permissions
# (Nécessaire si un volume Railway vide est monté sur /var/www/html/storage)
echo "▶ Préparation des répertoires de stockage..."
mkdir -p /var/www/html/storage/app/public \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Vérifier et importer automatiquement le schéma SQL si la base de données est vide
echo "▶ Vérification de la base de données PostgreSQL..."
php -r "
    try {
        \$pdo = new PDO('pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        \$stmt = \$pdo->query(\"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public' AND table_name='publications'\");
        \$exists = \$stmt->fetchColumn();
        if (\$exists == 0) {
            echo \"  ℹ️ Table 'publications' absente - Importation automatique du schéma SQL...\n\";
            \$file = '/var/www/html/ummisco_database.sql';
            if (file_exists(\$file)) {
                \$sql = file_get_contents(\$file);
                \$pdo->exec(\$sql);
                echo \"  ✅ Schéma SQL importé avec succès !\n\";
            } else {
                echo \"  ❌ Fichier ummisco_database.sql introuvable dans le conteneur.\n\";
            }
        } else {
            echo \"  ✅ Base de données Laravel déjà initialisée (table 'publications' présente).\n\";
        }
    } catch (Exception \$e) {
        echo \"  ⚠️ Impossible de vérifier ou d'initialiser la base de données : \" . \$e->getMessage() . \"\n\";
        exit(1); // Arrêter le conteneur en cas d'erreur critique de connexion base pour la voir dans les logs Railway
    }
"

# Optimisation Laravel pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Caches optimisés"

# Lancer Supervisor (Nginx + PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisord.conf
