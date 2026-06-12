# 🚂 Guide de Déploiement Complet — Railway

## Présentation

[Railway](https://railway.app) est une plateforme cloud performante. Grâce au fichier `docker-compose.yml` unifié présent à la racine du projet, Railway est capable de déployer et de lier **automatiquement tous les composants** de l'application UMMISCO (Laravel, PostgreSQL, Redis, MinIO, Keycloak).

---

## 🚀 Méthode de Déploiement Automatisée (Recommandée)

Puisque nous avons fusionné l'environnement de développement et de production dans un seul fichier `docker-compose.yml`, Railway va lire ce fichier et créer un "Service" pour chaque bloc défini.

### Étape 1 : Importer le projet sur Railway

1. Allez sur le [Dashboard Railway](https://railway.app/dashboard).
2. Cliquez sur **New Project** > **Deploy from GitHub Repo**.
3. Sélectionnez le dépôt `Portail-UMMISCO`.
4. Railway va détecter le `docker-compose.yml` et automatiquement créer 8 services :
   - `nginx` (Le proxy web public)
   - `app` (L'API Laravel / Frontend)
   - `scheduler` (Tâches cron)
   - `queue_worker` (Jobs asynchrones / Horizon)
   - `postgres` (Base de données)
   - `redis` (Cache et sessions)
   - `minio` (Stockage S3)
   - `keycloak` (Serveur d'authentification SSO)

### Étape 2 : Configuration des Volumes (Stockage Persistant)

Railway associera automatiquement des volumes persistants aux services qui en déclarent dans le `docker-compose.yml` (ex: `postgres_data`, `minio_data`). Aucune action n'est requise.

### Étape 3 : Injection des Variables de Production

Par défaut, le fichier utilise des valeurs de "Développement". Sur Railway, allez dans **Settings > Variables** de chaque service ou dans **Shared Variables** (Variables partagées du projet) pour imposer la configuration de production :

#### Variables Partagées (Shared Variables)
Ajoutez ces variables au niveau de l'environnement global Railway pour qu'elles s'appliquent à tous les services :

| Clé | Valeur (Exemple) |
|-----|------------------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://portail.ummisco.sn` |
| `DB_PASSWORD` | *(Générez un mot de passe fort)* |
| `REDIS_PASSWORD` | *(Générez un mot de passe fort)* |
| `MINIO_ROOT_PASSWORD`| *(Générez un mot de passe fort)* |
| `KEYCLOAK_ADMIN_PASSWORD` | *(Générez un mot de passe fort)* |
| `KEYCLOAK_COMMAND` | `start --optimized` *(Active le mode prod de Keycloak)* |
| `KEYCLOAK_HOSTNAME` | `auth.ummisco.sn` |

*(Note : Le `docker-compose.yml` reliera automatiquement `app` à `postgres` et `redis` car ils partagent les mêmes variables réseaux internes).*

### Étape 4 : Exposition des Domaines (Networking)

Sur Railway, par défaut, tous les services sont privés. Vous devez exposer uniquement les services qui doivent être accessibles depuis l'extérieur.

1. Allez dans le service **`nginx`** > **Settings** > **Networking**.
2. Cliquez sur **Generate Domain** (ex: `ummisco-nginx.up.railway.app`) ou ajoutez votre domaine personnalisé (`portail.ummisco.sn`).
3. Allez dans le service **`keycloak`** > **Settings** > **Networking**.
4. Exposez-le sur `auth.ummisco.sn`.
5. Allez dans le service **`minio`** > **Settings** > **Networking**.
6. Exposez le port 9000 sur `storage.ummisco.sn` (API S3) et le port 9001 sur `console.storage.ummisco.sn` (Interface Web).

### Étape 5 : Initialisation de la Base de Données

Le `docker-compose.yml` inclut déjà le montage du fichier `ummisco_database.sql`. Lors du premier démarrage du service `postgres` sur Railway, la base de données sera **automatiquement initialisée** avec l'intégralité du schéma (tables, types enum, triggers d'audit).

Pour forcer les optimisations Laravel au premier lancement, allez dans le service **`app`** > **Deploy** > **Start Command** et insérez :
```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache && php-fpm
```

---

## 🔧 Architecture de la Configuration Unifiée

### Pourquoi un seul fichier `docker-compose.yml` ?
1. **Source de vérité unique** : Fini la désynchronisation entre le mode local et la prod.
2. **Fallback natif** : Nous utilisons la syntaxe `${VARIABLE:-valeur_par_defaut}`.
   - En local, si aucune variable n'est définie, Laravel démarre avec la base locale `ummisco_app`, le user `ummisco_user`.
   - Sur Railway, les variables injectées écrasent les valeurs par défaut.
3. **Optimisation des coûts** : Railway facture à l'usage. En encapsulant le tout dans un seul compose, l'orchestration interne est gérée efficacement.

### Spécificités de développement
En mode développement local, le code source (`./app`) est monté via un volume vers `/var/www/html`. Sur Railway, ce montage de code local est ignoré au profit du code compilé directement dans l'image Docker.

---

## ✅ Checklist de Validation en Production

1. [ ] **Accès public** : Le domaine pointe bien vers le conteneur `nginx`.
2. [ ] **SSO** : Keycloak est fonctionnel en mode `start --optimized` et la page de login s'affiche via `auth.ummisco.sn`.
3. [ ] **MinIO** : Le stockage d'objets répond sur `storage.ummisco.sn`.
4. [ ] **Cron** : Le service `scheduler` ne génère pas d'erreur de connexion à la DB.
5. [ ] **Performances** : Le service `redis` est bien utilisé pour la session (`SESSION_DRIVER=redis`).
