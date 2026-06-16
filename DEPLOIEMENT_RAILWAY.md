# 🚂 Guide de Déploiement Complet de A à Z — Railway

Ce document est le guide de référence ultime pour déployer **l'intégralité** de l'infrastructure du Portail UMMISCO sur la plateforme Cloud [Railway](https://railway.app).

---

## 1. Architecture de Production sur Railway

Grâce au fichier `docker-compose.yml` unifié, Railway est capable de déployer et lier automatiquement tous les composants. L'infrastructure finale comprendra les services suivants :

*   **`nginx`** : Le proxy web (Point d'entrée public du portail).
*   **`app`** : L'API Laravel et le frontend compilé.
*   **`postgres`** : La base de données avec la recherche Full-Text.
*   **`redis`** : Le cache, les sessions et le courtier de messages.
*   **`scheduler`** : Déclenche les tâches récurrentes (Cron).
*   **`queue_worker`** : Traite les imports lourds en arrière-plan (DataCite/OpenAlex).
*   **`minio`** : Serveur de stockage d'objets (S3) pour les PDF et datasets.
*   **`keycloak`** : Serveur d'authentification centralisé (SSO).

---

## 2. ÉTAPE 1 : Déploiement Initial (Le Cœur)

1. Connectez-vous sur le [Dashboard Railway](https://railway.app/dashboard).
2. Cliquez sur **New Project** > **Deploy from GitHub Repo**.
3. Sélectionnez le dépôt `Portail-UMMISCO`.
4. Railway détecte le fichier `docker-compose.yml` et lance la création des 8 services.
5. **Volumes persistants** : Railway associe automatiquement des volumes persistants aux services qui le nécessitent (`postgres_data`, `minio_data`).

---

## 3. ÉTAPE 2 : Exposition des Domaines (Networking)

Sur Railway, tous les services sont privés par défaut. Vous devez rendre publics uniquement ceux qui interagissent avec les utilisateurs.

1. **Service `nginx` (Le portail Web)**
   * Allez dans le service `nginx` > **Settings** > **Networking**.
   * Cliquez sur **Generate Domain** (ex: `portail-ummisco.up.railway.app` ou ajoutez votre domaine personnalisé).
2. **Service `keycloak` (Authentification)**
   * Allez dans `keycloak` > **Settings** > **Networking**.
   * Générez un domaine (ex: `auth-ummisco.up.railway.app`).
3. **Service `minio` (Stockage S3)**
   * Allez dans `minio` > **Settings** > **Networking**.
   * Exposez le port `9000` (API S3) et générez le domaine `storage-ummisco.up.railway.app`.
   * Cliquez sur **Expose Another Port**, exposez le port `9001` (Console d'administration) et générez le domaine `console-storage-ummisco.up.railway.app`.

---

## 4. ÉTAPE 3 : Configuration Globale (Variables Partagées)

Pour que les conteneurs basculent du mode "Développement" au mode "Production", utilisez les **Shared Variables** (Variables Partagées) de Railway. Elles s'appliqueront à tous les services.

Allez dans les paramètres globaux du projet (Cmd/Ctrl + K > *Shared Variables*) et ajoutez :

| Clé | Valeur | Description |
|-----|--------|-------------|
| `APP_ENV` | `production` | Active les optimisations Laravel |
| `APP_DEBUG` | `false` | Désactive l'affichage des erreurs critiques |
| `APP_URL` | `https://portail-ummisco.up.railway.app` | L'URL générée à l'étape 2 pour Nginx |
| `DB_PASSWORD` | `votre_mdp_postgres` | Mot de passe robuste pour la base |
| `REDIS_PASSWORD` | `votre_mdp_redis` | Mot de passe robuste pour Redis |
| `MINIO_ROOT_PASSWORD` | `votre_mdp_minio` | Mot de passe d'administration S3 |
| `KEYCLOAK_ADMIN_PASSWORD`| `votre_mdp_keycloak` | Mot de passe administrateur SSO |
| `SESSION_SECURE_COOKIE` | `true` | Obligatoire en HTTPS pour l'OIDC |

*(Note : Laravel, Postgres et Redis communiqueront via le réseau interne de Railway sans avoir besoin d'exposer leurs ports).*

---

## 5. ÉTAPE 4 : Configuration Spécifique de Keycloak

Pour que Keycloak fonctionne derrière le proxy SSL de Railway, vous devez lui injecter ces variables spécifiques dans l'onglet **Variables** du service `keycloak` :

*   `KC_PROXY_HEADERS` = `xforwarded` (Critique)
*   `KC_HOSTNAME` = `auth-ummisco.up.railway.app`
*   `KC_HTTP_ENABLED` = `true`
*   `KC_DB` = `postgres`
*   `KC_DB_URL` = `jdbc:postgresql://${{Postgres.PGHOST}}:${{Postgres.PGPORT}}/${{Postgres.PGDATABASE}}`
*   `KC_DB_USERNAME` = `${{Postgres.PGUSER}}`
*   `KC_DB_PASSWORD` = `${{Postgres.PGPASSWORD}}`
*   `JAVA_OPTS_APPEND` = `-Xms128m -Xmx200m -XX:MaxMetaspaceSize=96m` (Évite le crash mémoire)

**Start Command** (Dans l'onglet Deploy du service Keycloak) :
```bash
/opt/keycloak/bin/kc.sh start --optimized
```

### 5.1 Création du Client OIDC dans Keycloak
Une fois Keycloak démarré, connectez-vous sur l'URL de sa console, créez le Realm `ummisco`, puis le client `laravel-app`. 
Assurez-vous que les **Valid redirect URIs** pointent vers `https://portail-ummisco.up.railway.app/auth/callback`.

### 5.2 Lier Laravel à Keycloak
Dans les variables partagées de Railway, ajoutez la configuration OIDC :
*   `KEYCLOAK_MOCK` = `false`
*   `KEYCLOAK_BASE_URL` = `https://auth-ummisco.up.railway.app`
*   `KEYCLOAK_CLIENT_SECRET` = `(le secret généré dans l'interface Keycloak)`
*   `KEYCLOAK_REDIRECT_URI` = `https://portail-ummisco.up.railway.app/auth/callback`

---

## 6. ÉTAPE 5 : Configuration Spécifique de MinIO

Connectez-vous à l'URL de la console MinIO (ex: `https://console-storage-ummisco.up.railway.app`) avec `minio_admin` et votre `MINIO_ROOT_PASSWORD`.
Créez un bucket nommé **`ummisco-public`**.

Ensuite, liez Laravel à MinIO via les variables partagées de Railway :

*   `FILESYSTEM_DISK` = `minio`
*   `MINIO_ENDPOINT` = `http://minio.railway.internal:9000` (Communication interne ultra-rapide)
*   `MINIO_KEY` = `minio_admin`
*   `MINIO_SECRET` = `(votre_mdp_minio)`
*   `MINIO_BUCKET_PUBLIC` = `ummisco-public`
*   `MINIO_USE_PATH_STYLE` = `true`

---

## 7. ÉTAPE 6 : Initialisation de l'Application (Post-Déploiement)

La base de données est initialisée automatiquement avec le schéma grâce au montage de `ummisco_database.sql` dans Postgres.

Pour forcer les optimisations Laravel et vérifier les migrations, allez dans le service **`app`** > **Deploy** > **Start Command** et insérez la commande de production :

```bash
php artisan optimize && php artisan view:cache && php-fpm
```

*(Si la base de données semble vide, exécutez la commande `php artisan migrate:fresh --seed` depuis le terminal Railway du service `app`).*

---

## 8. ✅ Checklist de Validation

1. [ ] **Portail Web** : `https://portail-ummisco.up.railway.app` s'affiche correctement sans erreur 500.
2. [ ] **Authentification** : Le bouton "Connexion" redirige bien vers Keycloak (`auth-ummisco...`) en HTTPS, et la redirection retour fonctionne.
3. [ ] **Stockage MinIO** : Les documents importés se téléchargent bien depuis le bucket interne.
4. [ ] **Tâches Asynchrones** : Le service `queue_worker` affiche "Processing..." dans ses logs lors d'une synchronisation ORCID.
5. [ ] **Performances** : La session est bien maintenue par le service `redis`.
