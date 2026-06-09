# 📖 Documentation technique — Portail Web UMMISCO

**Version** : 1.0  
**Date** : Juin 2025  
**Auteur** : Équipe DIC1 — Semestre 2 IPDL  
**Organisation** : UMMISCO — CNRS / IRD / UCAD

---

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Architecture technique](#2-architecture-technique)
3. [Stack technologique](#3-stack-technologique)
4. [Structure du projet](#4-structure-du-projet)
5. [Schéma de la base de données](#5-schéma-de-la-base-de-données)
6. [Modules applicatifs](#6-modules-applicatifs)
7. [Système d'authentification](#7-système-dauthentification)
8. [Règles métier](#8-règles-métier)
9. [API des routes](#9-api-des-routes)
10. [Sécurité](#10-sécurité)
11. [Tests](#11-tests)
12. [Annexes](#12-annexes)

---

## 1. Vue d'ensemble

### 1.1 Objectif

Le Portail Web UMMISCO est une application institutionnelle destinée à centraliser et valoriser les productions scientifiques de l'Unité Mixte Internationale de Modélisation Mathématique et Informatique des Systèmes Complexes (UMMISCO).

### 1.2 Périmètre fonctionnel

- **Portail public** : vitrine institutionnelle avec publications, axes de recherche, actualités
- **Espace authentifié** : soumission de contenus, gestion de profils, workflow de validation
- **Administration** : gestion des utilisateurs, validation des soumissions, audit
- **Fonctionnalités avancées** : recherche full-text bilingue (FR/EN), chatbot IA (Ollama), gestion de datasets

### 1.3 Utilisateurs cibles

| Rôle | Description | Droits |
|------|-------------|--------|
| Visiteur | Utilisateur non authentifié | Lecture du portail public |
| Chercheur | Membre UMMISCO | Publication directe, profil |
| Doctorant | Étudiant en thèse | Soumission avec validation |
| Partenaire | Institution partenaire | Accès contenus partenaires |
| Admin d'axe | Responsable d'axe thématique | Validation des soumissions de son axe |
| Super admin | Administrateur global | Accès complet |

---

## 2. Architecture technique

### 2.1 Architecture conteneurisée

```
                    ┌─────────┐
                    │ Client  │
                    │ (Naviga-│
                    │  teur)  │
                    └────┬────┘
                         │ HTTP :8080
                    ┌────▼────┐
                    │  Nginx  │ Reverse Proxy
                    │  1.25   │
                    └────┬────┘
                         │ FastCGI :9000
                    ┌────▼────┐
                    │ PHP-FPM │ Laravel 11
                    │  8.3    │ (Application)
                    └────┬────┘
                         │
         ┌───────────────┼───────────────┐
         │               │               │
    ┌────▼────┐    ┌─────▼─────┐   ┌─────▼─────┐
    │PostgreSQL│    │   Redis   │   │   MinIO   │
    │   16    │    │     7     │   │   (S3)    │
    │(données)│    │(cache/    │   │(fichiers) │
    │         │    │ sessions) │   │           │
    └─────────┘    └───────────┘   └───────────┘

    ┌─────────┐    ┌───────────┐
    │ Ollama  │    │ Keycloak  │
    │ (LLM)   │    │   24      │
    │(chatbot)│    │  (Auth)   │
    └─────────┘    └───────────┘
```

### 2.2 Pattern architectural

- **Pattern MVC** avec Laravel 11
- **Architecture modulaire** : chaque domaine métier est isolé dans `app/Modules/`
- **Session-based auth** : Keycloak OAuth2 → session Laravel (pas de JWT côté client)
- **Recherche full-text** : colonnes `TSVECTOR` PostgreSQL générées automatiquement

---

## 3. Stack technologique

### 3.1 Backend

| Technologie | Version | Rôle |
|------------|---------|------|
| PHP | 8.3 | Langage serveur |
| Laravel | 11.x | Framework MVC |
| PostgreSQL | 16 | Base de données relationnelle |
| Redis | 7 | Cache, sessions, files d'attente |
| Composer | 2.7 | Gestionnaire de dépendances PHP |

### 3.2 Frontend

| Technologie | Rôle |
|------------|------|
| Blade | Moteur de templates Laravel |
| CSS vanilla | Design system personnalisé |
| Inter (Google Fonts) | Typographie |

### 3.3 Infrastructure

| Technologie | Version | Rôle |
|------------|---------|------|
| Docker | 24+ | Conteneurisation |
| Docker Compose | 2.x | Orchestration locale |
| Nginx | 1.25 | Reverse proxy |
| MinIO | latest | Stockage objet S3-compatible |
| Ollama | latest | LLM local (chatbot IA) |
| Keycloak | 24 | SSO / OAuth2 |

### 3.4 Dépendances PHP principales

| Package | Usage |
|---------|-------|
| `socialiteproviders/keycloak` | Authentification OAuth2 Keycloak |
| `laravel/horizon` | Monitoring des files d'attente |
| `laravel/telescope` | Débogage (dev uniquement) |
| `intervention/image` | Traitement d'images |
| `barryvdh/laravel-dompdf` | Génération de PDF |
| `league/flysystem-aws-s3-v3` | Stockage MinIO / S3 |
| `predis/predis` | Client Redis |

---

## 4. Structure du projet

```
portail_web/
├── app/                          # Code source Laravel
│   ├── app/
│   │   ├── helpers.php           # Fonctions utilitaires globales
│   │   ├── Modules/
│   │   │   ├── Audit/
│   │   │   │   └── Models/
│   │   │   │       └── AuditLog.php          # Logs d'audit (append-only)
│   │   │   ├── Auth/
│   │   │   │   ├── Controllers/
│   │   │   │   │   └── AuthController.php    # Login/logout Keycloak
│   │   │   │   └── Middleware/
│   │   │   │       └── KeycloakMiddleware.php # Protection des routes
│   │   │   ├── Content/
│   │   │   │   ├── Controllers/
│   │   │   │   │   └── WorkflowValidationController.php
│   │   │   │   └── Models/
│   │   │   │       ├── Publication.php       # Modèle central
│   │   │   │       ├── WorkflowValidation.php
│   │   │   │       ├── Article.php
│   │   │   │       └── ...
│   │   │   ├── Dataset/
│   │   │   │   └── Models/
│   │   │   ├── Notification/
│   │   │   │   └── Models/
│   │   │   ├── PublicPortal/
│   │   │   │   └── Controllers/
│   │   │   │       └── PublicPortalController.php
│   │   │   └── User/
│   │   │       └── Models/
│   │   │           ├── User.php              # Modèle utilisateur
│   │   │           └── AxeThematique.php     # Axes de recherche
│   │   └── Providers/
│   │       └── AppServiceProvider.php
│   ├── bootstrap/
│   │   ├── app.php               # Configuration middleware et routes
│   │   └── providers.php         # Providers enregistrés
│   ├── config/                   # Fichiers de configuration (en français)
│   ├── resources/
│   │   ├── views/
│   │   │   ├── layouts/app.blade.php
│   │   │   ├── auth/mock-login.blade.php
│   │   │   ├── public/
│   │   │   │   ├── home.blade.php
│   │   │   │   ├── publications.blade.php
│   │   │   │   └── axes.blade.php
│   │   │   └── workflow/
│   │   │       └── pending.blade.php
│   │   └── lang/                 # Fichiers de traduction FR/EN
│   ├── routes/
│   │   ├── web.php               # Routes HTTP
│   │   └── console.php           # Commandes Artisan
│   ├── .env                      # Variables d'environnement
│   └── composer.json             # Dépendances PHP
├── docker/
│   ├── nginx/conf.d/             # Configuration Nginx
│   ├── php/
│   │   ├── Dockerfile            # Image PHP-FPM 8.3
│   │   └── php.ini               # Configuration PHP
│   └── postgres/
│       └── init.sql              # Script d'initialisation
├── docker-compose.yml            # Orchestration des conteneurs
├── ummisco_database.sql          # Schéma complet PostgreSQL
├── INSTALLATION.md               # Guide d'installation locale
├── DEPLOIEMENT_RAILWAY.md        # Guide de déploiement Railway
└── README.md                     # Présentation générale
```

---

## 5. Schéma de la base de données

### 5.1 Vue d'ensemble

- **35 tables** PostgreSQL 16
- **9 types ENUM** personnalisés
- **4 extensions** : `uuid-ossp`, `pg_trgm`, `unaccent`, `btree_gin`
- **Colonnes TSVECTOR** générées automatiquement pour la recherche full-text

### 5.2 Tables principales

| Table | Description | Clé primaire |
|-------|-------------|-------------|
| `users` | Utilisateurs (tous rôles) | UUID |
| `publications` | Contenus scientifiques | UUID |
| `axes_thematiques` | Axes de recherche UMMISCO | UUID |
| `workflow_validations` | Cycle de validation des soumissions | UUID |
| `audit_logs` | Journal d'audit (append-only, immuable) | UUID |
| `users_axes` | Relation N:N utilisateurs ↔ axes | Composite |
| `publications_auteurs` | Co-auteurs des publications | Composite |
| `datasets` | Jeux de données scientifiques | UUID |
| `medias` | Fichiers médias (stockés sur MinIO) | UUID |
| `parametres_systeme` | Configuration dynamique | Clé texte |

### 5.3 Types ENUM PostgreSQL

```sql
CREATE TYPE user_role AS ENUM (
    'visitor', 'researcher', 'doctoral_student',
    'partner', 'axe_admin', 'super_admin'
);

CREATE TYPE publication_status AS ENUM (
    'draft', 'submitted', 'under_review',
    'published', 'archived', 'rejected'
);

CREATE TYPE publication_type AS ENUM (
    'article', 'document', 'event', 'dataset',
    'news', 'thesis', 'report', 'presentation'
);

CREATE TYPE visibility AS ENUM (
    'public', 'partners', 'internal'
);

CREATE TYPE workflow_status AS ENUM (
    'pending', 'approved', 'rejected', 'revision_required'
);

CREATE TYPE audit_action AS ENUM (
    'create', 'update', 'delete', 'publish', 'submit',
    'approve', 'reject', 'login', 'logout', 'download',
    'acl_change', 'password_reset'
);
```

### 5.4 Recherche full-text

Les colonnes `fts_fr` et `fts_en` dans la table `publications` sont de type `TSVECTOR GENERATED ALWAYS`. Elles sont alimentées automatiquement par PostgreSQL à chaque insertion/mise à jour et indexées avec des index GIN pour des recherches rapides.

```sql
-- Exemple de recherche full-text
SELECT * FROM publications
WHERE fts_fr @@ plainto_tsquery('french', 'modélisation épidémie')
ORDER BY ts_rank(fts_fr, plainto_tsquery('french', 'modélisation épidémie')) DESC;
```

### 5.5 Intégrité des données

| Mécanisme | Table | Description |
|-----------|-------|-------------|
| Trigger `fn_audit_logs_immutable()` | `audit_logs` | Bloque UPDATE et DELETE au niveau SQL |
| Soft Delete | `users`, `publications` | Colonne `deleted_at` (suppression logique) |
| Contraintes FK | Toutes | Intégrité référentielle avec `ON DELETE` approprié |
| Contraintes CHECK | Diverses | Validation des données (emails, URLs, etc.) |

---

## 6. Modules applicatifs

### 6.1 Module Auth (`app/Modules/Auth/`)

**Responsabilité** : Authentification et autorisation via Keycloak SSO.

| Composant | Description |
|-----------|-------------|
| `AuthController` | Gère le flux OAuth2 : redirect → callback → logout |
| `KeycloakMiddleware` | Protège les routes par vérification de session et de rôle |

**Mode Mock** : En développement (`KEYCLOAK_MOCK=true`), un formulaire permet de sélectionner un rôle sans serveur Keycloak.

### 6.2 Module PublicPortal (`app/Modules/PublicPortal/`)

**Responsabilité** : Pages accessibles sans authentification.

| Action | Route | Description |
|--------|-------|-------------|
| `home()` | `GET /` | Page d'accueil avec axes, publications récentes, statistiques |
| `publications()` | `GET /publications` | Catalogue avec filtres full-text, type, axe |
| `axes()` | `GET /axes` | Liste des axes de recherche |
| `subscribeNewsletter()` | `POST /newsletter/subscribe` | Inscription newsletter |
| `submitContact()` | `POST /contact` | Formulaire de contact |

### 6.3 Module Content (`app/Modules/Content/`)

**Responsabilité** : Gestion des publications et workflow de validation.

| Action | Route | Rôle requis | Description |
|--------|-------|-------------|-------------|
| `submit()` | `POST /publications/submit` | Authentifié | Soumet une publication |
| `approve()` | `POST /workflow/{id}/approve` | Admin d'axe | Approuve une soumission |
| `reject()` | `POST /workflow/{id}/reject` | Admin d'axe | Rejette avec motif |

### 6.4 Module Audit (`app/Modules/Audit/`)

**Responsabilité** : Journal d'audit immuable (RG-018).

- Toute action importante est journalisée via `AuditLog::log()`
- La table est append-only : ni UPDATE ni DELETE autorisés
- Protection double : trigger PostgreSQL + exception Eloquent

### 6.5 Module User (`app/Modules/User/`)

**Responsabilité** : Gestion des utilisateurs et axes thématiques.

- Modèle `User` avec 6 rôles ENUM et soft delete
- Modèle `AxeThematique` avec responsable, membres et publications
- Profils spécialisés : `ProfilChercheur`, `ProfilDoctorant`, `ProfilPartenaire`

---

## 7. Système d'authentification

### 7.1 Flux OAuth2 (Keycloak)

```
Utilisateur → GET /auth/login → Redirect Keycloak
    → Page de login Keycloak
    → Saisie identifiants
    → Redirect /auth/callback?code=XXX
    → Échange code → access_token (serveur à serveur)
    → Synchronisation utilisateur PostgreSQL
    → Création session Laravel
    → Redirect page d'accueil
```

### 7.2 Gestion des sessions

| Donnée | Clé session | Description |
|--------|-------------|-------------|
| ID utilisateur | `user_id` | UUID PostgreSQL |
| Rôle | `user_role` | ENUM `user_role` |
| Email | `user_email` | Email Keycloak |
| Nom complet | `user_name` | Prénom + Nom |
| Token d'accès | `access_token` | Token Keycloak ou mock |
| Langue | `locale` | `fr` ou `en` |

### 7.3 Middleware de sécurité

```php
// Vérification d'authentification simple
Route::middleware([KeycloakMiddleware::class])->group(...)

// Vérification d'authentification + rôle spécifique
Route::middleware(['role:axe_admin,super_admin'])->group(...)
```

---

## 8. Règles métier

| Code | Règle | Implémentation |
|------|-------|----------------|
| **RG-009** | Un doctorant ne peut publier directement | `User::requiresWorkflow()` → `WorkflowValidationController::submit()` |
| **RG-011** | Le validateur est l'admin de l'axe thématique | Vérification `axe.responsable_id === userId` dans `approve()` et `reject()` |
| **RG-012** | Un chercheur peut publier directement | `User::canPublishDirectly()` → statut `published` immédiat |
| **RG-014** | Tout rejet doit être accompagné d'un motif | Validation `required` sur `commentaire_admin` dans `reject()` |
| **RG-018** | Les logs d'audit sont immuables | Trigger SQL + exception Eloquent bloquant UPDATE/DELETE |

---

## 9. API des routes

### 9.1 Routes publiques (sans authentification)

| Méthode | URI | Nom | Contrôleur |
|---------|-----|-----|------------|
| `GET` | `/` | `home` | `PublicPortalController@home` |
| `GET` | `/publications` | `publications` | `PublicPortalController@publications` |
| `GET` | `/axes` | `axes` | `PublicPortalController@axes` |
| `POST` | `/newsletter/subscribe` | `newsletter.subscribe` | `PublicPortalController@subscribeNewsletter` |
| `POST` | `/contact` | `contact.submit` | `PublicPortalController@submitContact` |

### 9.2 Routes d'authentification

| Méthode | URI | Nom | Contrôleur |
|---------|-----|-----|------------|
| `GET` | `/auth/login` | `login` | `AuthController@redirect` |
| `GET/POST` | `/auth/callback` | `auth.callback` | `AuthController@callback` |
| `POST` | `/auth/logout` | `logout` | `AuthController@logout` |

### 9.3 Routes sécurisées (KeycloakMiddleware)

| Méthode | URI | Nom | Contrôleur |
|---------|-----|-----|------------|
| `POST` | `/publications/submit` | `publications.submit` | `WorkflowValidationController@submit` |
| `POST` | `/workflow/{id}/approve` | `workflow.approve` | `WorkflowValidationController@approve` |
| `POST` | `/workflow/{id}/reject` | `workflow.reject` | `WorkflowValidationController@reject` |
| `GET` | `/workflow/pending` | `workflow.pending` | Closure (inline) |

---

## 10. Sécurité

### 10.1 Mesures implémentées

| Mesure | Implémentation |
|--------|----------------|
| **Authentification** | Keycloak SSO (OAuth2 Authorization Code) |
| **Autorisation** | Middleware `KeycloakMiddleware` avec vérification de rôle |
| **CSRF** | Protection automatique Laravel sur toutes les routes POST |
| **XSS** | Échappement automatique Blade (`{{ }}`) |
| **Injection SQL** | Query builder Eloquent (requêtes paramétrées) |
| **Audit** | Journalisation de toutes les actions sensibles |
| **Soft Delete** | Suppression logique (pas de perte de données) |
| **Sessions** | Stockage Redis avec chiffrement optionnel |

### 10.2 En-têtes de sécurité

Configurés dans Nginx (`docker/nginx/conf.d/default.conf`) :
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`

### 10.3 Variables sensibles

Toutes les valeurs sensibles (mots de passe, clés API, secrets) sont stockées dans le fichier `.env` et **jamais versionnées** dans Git (`.gitignore`).

---

## 11. Tests

### 11.1 Exécuter les tests

```bash
# Tous les tests
docker compose exec app php artisan test

# Tests avec couverture
docker compose exec app php artisan test --coverage

# Tests spécifiques
docker compose exec app php artisan test --filter=AuthControllerTest
```

### 11.2 Analyse statique

```bash
# PHPStan (Larastan)
docker compose exec app ./vendor/bin/phpstan analyse

# Laravel Pint (formatage du code)
docker compose exec app ./vendor/bin/pint
```

---

## 12. Annexes

### 12.1 Variables d'environnement complètes

Voir le fichier `app/.env` pour la liste complète des variables avec leurs valeurs par défaut.

### 12.2 Fichiers de référence

| Fichier | Description |
|---------|-------------|
| `ummisco_database.sql` | Schéma complet PostgreSQL (35 tables, 9 ENUMs) |
| `docker-compose.yml` | Infrastructure Docker complète |
| `INSTALLATION.md` | Guide d'installation locale |
| `DEPLOIEMENT_RAILWAY.md` | Guide de déploiement Railway |
| `UMMISCO_Dossier_Conception.docx` | Dossier de conception original |

### 12.3 Contacts techniques

- **Email** : admin@ummisco.ucad.sn
- **Organisation** : Université Cheikh Anta Diop — Dakar, Sénégal
- **Laboratoire** : UMMISCO — CNRS / IRD / UCAD
