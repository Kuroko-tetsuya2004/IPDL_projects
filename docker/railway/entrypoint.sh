#!/bin/sh
set -e

echo "🚀 Démarrage du portail UMMISCO..."

# Vérifier et importer automatiquement le schéma SQL si la base de données est vide
echo "▶ Vérification de la base de données PostgreSQL..."
php -r "
    try {
        \$pdo = new PDO('pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
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
    }
"

# Optimisation Laravel pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Caches optimisés"

# Lancer Supervisor (Nginx + PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisord.conf
