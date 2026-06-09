-- Script d'initialisation PostgreSQL pour le portail UMMISCO
-- Exécuté automatiquement au premier démarrage du conteneur postgres

-- Créer la base de données de test
CREATE DATABASE ummisco_test;

-- Accorder les droits
GRANT ALL PRIVILEGES ON DATABASE ummisco_app TO ummisco_user;
GRANT ALL PRIVILEGES ON DATABASE ummisco_test TO ummisco_user;
