# 🛠 Guide d'Installation et de Configuration Global — Portail UMMISCO

Ce guide détaille l'intégralité des étapes nécessaires pour installer l'application de zéro, configurer l'ensemble de ses services sous-jacents (PostgreSQL, Redis, MinIO, Keycloak, etc.), et préparer l'environnement pour la production ou le développement.

---

## 1. Architecture des Services

L'application repose sur l'architecture de micro-services suivante, gérée par Docker :
- **App (PHP-FPM 8.2 & Laravel 11)** : Le cœur de l'application (Backend API & Logique Métier).
- **Vite (Node.js)** : Serveur de compilation des assets pour le frontend (Vue.js 3 / Inertia / TailwindCSS).
- **PostgreSQL 16** : Base de données relationnelle principale avec support de la recherche *Full-Text* et triggers de sécurité.
- **Redis 7** : Utilisé pour les sessions, le cache applicatif ultra-rapide et la gestion des files d'attente (Queues).
- **MinIO** *(Optionnel)* : Stockage d'objets compatible S3 (pour les PDF volumineux et jeux de données massifs).
- **Keycloak** *(Mode Mock localement, réel en Prod)* : Système d'authentification centralisé SSO (Single Sign-On).
- **Nginx** : Serveur web / Reverse Proxy frontal.

---

## 2. Prérequis Locaux

Avant de commencer, assurez-vous de disposer des éléments suivants sur la machine hôte :
- **[Docker Desktop](https://www.docker.com/products/docker-desktop/)** (en cours de fonctionnement).
- **[Git](https://git-scm.com/)**.
- Les ports **8080** (Web), **5432** (BDD) et **6379** (Redis) doivent être disponibles.

---

## 3. Étape 1 : Récupération et préparation du projet

Ouvrez un terminal et exécutez :

```bash
# 1. Cloner le dépôt et entrer dans le répertoire applicatif
git clone https://github.com/Kuroko-tetsuya2004/IPDL_projects.git portail_web
cd portail_web/app

# 2. Installer les dépendances PHP via un conteneur utilitaire léger
docker run --rm -v $(pwd):/app composer install

# 3. Installer les dépendances Javascript (Node/NPM)
docker run --rm -v $(pwd):/app -w /app node:20 npm install

# 4. Copier le fichier de configuration environnementale par défaut
cp .env.example .env
```

---

## 4. Étape 2 : Configuration Globale locale (`.env`)

Ouvrez le fichier `.env` fraîchement créé pour ajuster les configurations clés :

### 4.1 Base de Données (PostgreSQL)
Laissez les valeurs par défaut pour un fonctionnement "plug & play" avec Docker :
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=ummisco_app
DB_USERNAME=ummisco_user
DB_PASSWORD=secret
```

### 4.2 Cache, Sessions et Files d'attente (Redis)
Ces variables délèguent la charge mémoire à Redis :
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### 4.3 Intégrations APIs (OpenAlex & DataCite)
Pour éviter les blocages de requêtes (Rate Limits) lors des synchronisations ORCID massives, ajoutez un e-mail de contact :
```env
OPENALEX_EMAIL=admin@institution.fr
CROSSREF_MAILTO=admin@institution.fr
```

---

## 5. Étape 3 : Démarrage de l'Infrastructure Docker (Local)

Lancez tous les services en arrière-plan :

```bash
docker compose up -d
```

Vérifiez que les conteneurs (nginx, app, postgres, redis) sont à l'état `Up` :
```bash
docker compose ps
```

---

## 6. Étape 4 : Initialisation Métier de l'Application

Les conteneurs tournent, mais l'application Laravel nécessite l'injection de son cœur logique. Exécutez ces commandes en ciblant le conteneur `app` :

```bash
# 1. Générer la clé de chiffrement (Sécurité)
docker compose exec app php artisan key:generate

# 2. Créer le schéma de base de données et les règles d'audit (Triggers PostgreSQL)
docker compose exec app php artisan migrate:fresh

# 3. Injecter les données de démarrage (Axes thématiques, Rôles, Compte Super Admin)
docker compose exec app php artisan db:seed

# 4. Créer les liens symboliques pour rendre les fichiers uploadés (PDFs) accessibles
docker compose exec app php artisan storage:link
```

### Compilation du Frontend
Le navigateur a besoin de Javascript et de CSS purs. Choisissez l'une de ces commandes :
```bash
# Option A : Développement (Regénère automatiquement le code à chaque sauvegarde)
docker compose exec app npm run dev

# Option B : Production (Génère une version optimisée, légère et définitive)
docker compose exec app npm run build
```

---

## 7. Étape 5 : Lancement des Workers (Tâches d'Arrière-Plan)

L'importation de publications (via ORCID, OpenAlex) ou de Datasets lourds prend du temps et doit s'exécuter de façon asynchrone pour ne pas bloquer l'utilisateur.
Vous devez lancer un travailleur `Queue Worker` branché sur Redis :

```bash
docker compose exec -d app php artisan queue:work redis --tries=3 --timeout=120
```
*(En environnement de production réel, ce service sera maintenu actif via `Supervisor` ou Laravel Horizon).*

---

## 8. Utilisation et Vérification en Local 🎉

Le déploiement local est terminé !

* 🌐 **Ouvrez le portail Web** : [http://localhost:8080](http://localhost:8080)
* 🔐 **Connexion Admin (Mode Mock)** : 
  * Par défaut, l'environnement local simule Keycloak. Cliquez sur **Connexion**.
  * Si vous avez exécuté les `seeders`, choisissez le profil **"Super Administrateur"**.
  * Vous avez maintenant accès complet au tableau de bord.

---

## 9. Résolution des Problèmes (Troubleshooting)

* **Vous obtenez une Erreur 500 mystérieuse ou la vue ne s'actualise pas :**
  Videz tous les caches applicatifs.
  ```bash
  docker compose exec app php artisan optimize:clear
  ```
* **L'AuditLog plante (Erreurs de contrainte BDD) :**
  La table `audit_logs` est inviolable. En développement, si vous cherchez à modifier des entrées manuellement, PostgreSQL vous en empêchera. Privilégiez une remise à zéro globale :
  ```bash
  docker compose exec app php artisan migrate:fresh --seed
  ```
* **Le port 8080 est déjà occupé sur Windows :**
  Modifiez le fichier `docker-compose.yml` à la ligne correspondante au service `nginx` :
  ```yaml
  ports:
    - "8090:80"  # Vous accéderez alors via http://localhost:8090
  ```

---
---

# 10. 🔐 Configuration Keycloak (Docker Local) — Guide complet A à Z

> **Prérequis** : L'application tourne déjà en mode Mock (`KEYCLOAK_MOCK=true`).
> Ce guide te fait passer de zéro à une authentification Keycloak fonctionnelle en local.

---

## Vue d'ensemble

```
┌──────────────────────────────────────────────────────────┐
│  ÉTAPES                                                   │
│                                                           │
│  1. Ajouter Keycloak dans Docker                         │
│  2. Démarrer Keycloak                                     │
│  3. Créer le Realm "ummisco"                             │
│  4. Créer le Client "laravel-app"                        │
│  5. Créer les 6 rôles                                    │
│  6. Créer un utilisateur de test                         │
│  7. Récupérer le Client Secret                           │
│  8. Modifier app/.env                                    │
│  9. Désactiver le Mock et tester                         │
└──────────────────────────────────────────────────────────┘
```

---

## ÉTAPE 1 — Ajouter Keycloak dans docker-compose.yml

Ouvre `docker-compose.yml` et ajoute ce service à la fin, avant la fermeture du fichier :

```yaml
  # ── KEYCLOAK 24 — Serveur d'authentification ──────────────────────────────
  keycloak:
    image: quay.io/keycloak/keycloak:24.0
    container_name: ummisco_keycloak
    command: start-dev
    environment:
      KEYCLOAK_ADMIN: admin
      KEYCLOAK_ADMIN_PASSWORD: admin_secret
    ports:
      - "8180:8080"
    networks:
      - backend_net
      - frontend_net
```

> ⚠️ Le port `8180` est utilisé pour éviter le conflit avec Nginx qui tourne sur `8080`.

---

## ÉTAPE 2 — Démarrer Keycloak

```powershell
cd C:\Users\USER\Documents\DIC1\Semestre2\IPDL\portail_web
docker compose up -d keycloak
```

Attends environ **30 secondes** que Keycloak démarre, puis vérifie :

```powershell
docker compose logs keycloak --tail=20
```

Tu dois voir une ligne contenant :
```
Keycloak 24.0 on JVM ... started in ...
```

Ouvre ensuite **http://localhost:8180** dans ton navigateur.

---

## ÉTAPE 3 — Se connecter à l'interface d'administration

1. Aller sur **http://localhost:8180**
2. Cliquer sur **« Administration Console »**
3. Se connecter avec :
   - **Username** : `admin`
   - **Password** : `admin_secret`

---

## ÉTAPE 4 — Créer le Realm `ummisco`

> Un Realm est un espace isolé qui contient tes utilisateurs, rôles et clients.

1. En haut à gauche, tu vois **« Keycloak »** (realm par défaut)
2. Cliquer dessus → **« Create realm »**
3. Remplir le formulaire :
   - **Realm name** : `ummisco`
   - **Enabled** : ✅ (activé)
4. Cliquer **« Create »**

Tu es maintenant dans le realm **ummisco** (visible en haut à gauche).

---

## ÉTAPE 5 — Créer le Client `laravel-app`

> Le Client représente ton application Laravel auprès de Keycloak.

### 5.1 — Créer le client

1. Dans le menu gauche → **Clients** → **« Create client »**

### 5.2 — Étape 1 : General settings

| Champ | Valeur |
|-------|--------|
| **Client type** | `OpenID Connect` |
| **Client ID** | `laravel-app` |
| **Name** | `Portail UMMISCO` |
| **Description** | `Application Laravel du portail` |

Cliquer **« Next »**

### 5.3 — Étape 2 : Capability config

| Paramètre | Valeur |
|-----------|--------|
| **Client authentication** | ✅ **On** (obligatoire pour avoir un secret) |
| **Authorization** | ❌ Off |
| **Standard flow** | ✅ On |
| **Direct access grants** | ✅ On |
| Tout le reste | ❌ Off |

Cliquer **« Next »**

### 5.4 — Étape 3 : Login settings

| Champ | Valeur |
|-------|--------|
| **Root URL** | `http://localhost:8080` |
| **Home URL** | `http://localhost:8080` |
| **Valid redirect URIs** | `http://localhost:8080/auth/callback` |
| **Valid post logout redirect URIs** | `http://localhost:8080` |
| **Web origins** | `http://localhost:8080` |

Cliquer **« Save »**

---

## ÉTAPE 6 — Créer les 6 rôles du Realm

> ⚠️ Les rôles doivent être des **Realm roles** (pas des Client roles).

1. Dans le menu gauche → **Realm roles** → **« Create role »**
2. Créer les 6 rôles suivants (un par un) :

| **Role name** | **Description** |
|---------------|-----------------|
| `visitor` | Visiteur — accès lecture seule |
| `researcher` | Chercheur — publication directe |
| `doctoral_student` | Doctorant — soumissions avec validation |
| `partner` | Partenaire externe |
| `axe_admin` | Administrateur d'axe thématique |
| `super_admin` | Super Administrateur — accès complet |

Pour chaque rôle :
1. Cliquer **« Create role »**
2. Saisir le **Role name** (exactement comme dans le tableau)
3. Cliquer **« Save »**

---

## ÉTAPE 7 — Créer un utilisateur de test

1. Dans le menu gauche → **Users** → **« Create new user »**
2. Remplir :
   - **Username** : `admin.test`
   - **Email** : `admin@ummisco.ucad.sn`
   - **First name** : `Admin`
   - **Last name** : `UMMISCO`
   - **Email verified** : ✅ On
3. Cliquer **« Create »**

### 7.1 — Définir un mot de passe

1. Aller sur l'onglet **« Credentials »**
2. Cliquer **« Set password »**
3. Saisir :
   - **Password** : `Test1234!`
   - **Password confirmation** : `Test1234!`
   - **Temporary** : ❌ Off
4. Cliquer **« Save »** → **« Save password »**

### 7.2 — Assigner le rôle `super_admin`

1. Aller sur l'onglet **« Role mapping »**
2. Cliquer **« Assign role »**
3. S'assurer que le filtre est sur **« Filter by realm roles »**
4. Cocher `super_admin`
5. Cliquer **« Assign »**

---

## ÉTAPE 8 — Récupérer le Client Secret

1. Dans le menu gauche → **Clients** → `laravel-app`
2. Aller sur l'onglet **« Credentials »**
3. Copier la valeur du champ **« Client secret »**

> 📋 Garde ce secret de côté, tu en as besoin à l'étape suivante.

---

## ÉTAPE 9 — Modifier app/.env

Ouvre `app/.env` et remplace la section Keycloak par :

```env
# ── KEYCLOAK ──────────────────────────────────────────────────────────────────
KEYCLOAK_BASE_URL=http://keycloak:8080
KEYCLOAK_REALM=ummisco
KEYCLOAK_CLIENT_ID=laravel-app
KEYCLOAK_CLIENT_SECRET=COLLER_LE_SECRET_DE_LETAPE_8_ICI
KEYCLOAK_REDIRECT_URI=http://localhost:8080/auth/callback
KEYCLOAK_MOCK=false
```

> ⚠️ `KEYCLOAK_BASE_URL=http://keycloak:8080` utilise le **nom du service Docker** (réseau interne).
> Ne pas mettre `localhost:8180` ici — c'est le port externe pour ton navigateur uniquement.

---

## ÉTAPE 10 — Vider le cache et redémarrer

```powershell
# Vider le cache de config Laravel
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

# Redémarrer l'app
docker compose restart app
```

---

## ÉTAPE 11 — Tester la connexion

1. Ouvrir **http://localhost:8080**
2. Cliquer **« Connexion »**
3. Tu dois être **redirigé vers Keycloak** (page de login Keycloak, pas le formulaire Mock)
4. Se connecter avec :
   - **Username** : `admin.test`
   - **Password** : `Test1234!`
5. Tu dois être redirigé vers le portail connecté en tant que **Super Administrateur**

---

## Retourner en mode Mock (si besoin)

```powershell
# Dans app/.env, changer :
KEYCLOAK_MOCK=true

# Puis vider le cache
docker compose exec app php artisan config:clear
```

---

## Mapping des rôles Keycloak → Application

L'application lit le claim JWT `realm_access.roles` envoyé par Keycloak et applique le premier rôle trouvé dans cet ordre de priorité :

| Priorité | Rôle Keycloak | Rôle dans l'app |
|----------|--------------|-----------------|
| 1 (max) | `super_admin` | Super Administrateur |
| 2 | `axe_admin` | Administrateur d'axe |
| 3 | `researcher` | Chercheur |
| 4 | `doctoral_student` | Doctorant |
| 5 | `partner` | Partenaire |
| 6 (défaut) | *(aucun)* | Visiteur |

---

## Dépannage Keycloak

### « Invalid redirect_uri »
La valeur dans **Clients → laravel-app → Valid redirect URIs** doit être **exactement** :
`http://localhost:8080/auth/callback`

### « Client secret is not valid »
1. **Clients → laravel-app → Credentials → Regenerate**
2. Copier le nouveau secret dans `app/.env`
3. `docker compose exec app php artisan config:clear`

### « Realm does not exist »
Vérifier que `KEYCLOAK_REALM=ummisco` correspond exactement au nom du Realm créé à l'étape 4.

### L'utilisateur n'a pas le bon rôle après connexion
1. **Users → [utilisateur] → Role mapping** → vérifier que le rôle est bien assigné
2. Les rôles doivent être des **Realm roles**, pas des Client roles
3. Vérifier le token JWT : **Clients → laravel-app → Client scopes → Evaluate → Generated access token**
   → Le champ `realm_access.roles` doit contenir ton rôle

### Keycloak inaccessible depuis l'app (connexion refusée)
Vérifier que Keycloak et l'app sont sur le même réseau Docker :
```powershell
docker compose exec app curl http://keycloak:8080
```
Si erreur → vérifier que `keycloak` est dans `networks: backend_net` ET `frontend_net`.
