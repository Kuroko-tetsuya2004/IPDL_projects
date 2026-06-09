# PORTAIL WEB UMMISCO

> Portail institutionnel du laboratoire UMMISCO — Université Cheikh Anta Diop de Dakar  
> **Stack :** Laravel 11 · Vue.js 3 · Inertia.js · PostgreSQL 16 · Redis 7 · Keycloak 24

---

## ⚡ Démarrage rapide (5 minutes)

### Prérequis

| Outil | Version minimale |
|---|---|
| Docker Desktop | 24.x |
| Docker Compose | v2.x |
| Git | 2.x |

> PHP et Composer ne sont **pas** nécessaires sur votre machine — tout tourne dans Docker.

### 1. Cloner et démarrer

```powershell
git clone https://github.com/ummisco/portail-web.git
cd portail-web

# Lancer le projet (installe tout automatiquement)
.\start.ps1
```

### 2. Vérifier la connexion à la base de données

La base de données est **automatiquement initialisée** depuis `ummisco_database.sql` au premier démarrage du conteneur PostgreSQL.

```powershell
# Vérifier que les tables sont créées
docker exec ummisco_postgres psql -U ummisco_user -d ummisco_app -c "\dt"

# Vérifier les ENUMs créés
docker exec ummisco_postgres psql -U ummisco_user -d ummisco_app -c "\dT"

# Compter les tables (doit afficher ~35)
docker exec ummisco_postgres psql -U ummisco_user -d ummisco_app -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public';"
```

### 3. Accéder aux services

| Service | URL | Identifiants |
|---|---|---|
| Portail public | http://localhost:8080 | — |
| MinIO Console | http://localhost:9001 | minio_admin / minio_secret_2024 |
| Ollama API | http://localhost:11434 | — |
| PostgreSQL | localhost:5432 | ummisco_user / ummisco_secret |
| Redis | localhost:6379 | redis_ummisco_secret |

---

## 🗄️ Base de données

### Schéma

Le fichier `ummisco_database.sql` contient le schéma complet PostgreSQL 16 :

| Objet | Quantité |
|---|---|
| Tables | 35 |
| Types ENUM | 9 |
| Extensions | 4 (uuid-ossp, pg_trgm, unaccent, btree_gin) |
| Index | 30+ |
| Triggers | 15+ |
| Vues | 6 |
| Fonctions | 7 |

### Tables principales

| Table | Description |
|---|---|
| `users` | Tous les utilisateurs (chercheurs, doctorants, partenaires, admins) |
| `axes_thematiques` | Axes de recherche (épidémio, climat, FabLab, IoT, méthodes) |
| `publications` | Table centrale — tous les contenus |
| `datasets` | Jeux de données scientifiques |
| `workflow_validations` | Cycle de validation pour les doctorants |
| `controle_acces` | ACL granulaires par ressource |
| `outils_doctoraux` | Outils externes intégrés via iframe |
| `audit_logs` | Journal immuable (append-only) |
| `notifications` | Notifications asynchrones |
| `chatbot_sessions` | Sessions chatbot (messages non persistés — RG-022) |

### Se connecter manuellement à PostgreSQL

```powershell
# Via Docker
docker exec -it ummisco_postgres psql -U ummisco_user -d ummisco_app

# Depuis un client externe (DBeaver, TablePlus, pgAdmin)
Host: localhost
Port: 5432
Database: ummisco_app
Username: ummisco_user
Password: ummisco_secret
```

### Réinitialiser la base de données

```powershell
# ⚠️ Supprime TOUTES les données !
docker compose down -v          # Supprime les volumes
docker compose up -d postgres   # Redémarre (reimporte ummisco_database.sql automatiquement)
```

---

## 🔧 Commandes utiles

```powershell
# Démarrer tous les services
docker compose up -d

# Arrêter tous les services
docker compose down

# Voir les logs de l'application
docker compose logs -f app

# Exécuter une commande Artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan cache:clear
docker compose exec app php artisan queue:work

# Accéder au shell du conteneur Laravel
docker compose exec app sh

# Télécharger un modèle LLM pour Ollama
docker compose exec ollama ollama pull llama3
```

---

## 📁 Structure du projet

```
portail_web/
├── app/                          # Application Laravel 11
│   ├── app/
│   │   ├── Modules/              # 13 modules métier
│   │   │   ├── Auth/             # M01 — Authentification Keycloak
│   │   │   ├── User/             # M02 — Profils utilisateurs
│   │   │   ├── PublicPortal/     # M03 — Portail public + i18n
│   │   │   ├── Content/          # M04 — Publications + Workflow
│   │   │   ├── Dataset/          # M05 — Datasets MinIO
│   │   │   ├── AxeThematique/    # M06 — Portails thématiques
│   │   │   ├── Admin/            # M07 — Back-office
│   │   │   ├── ControleAcces/    # M08 — ACL + Keycloak sync
│   │   │   ├── Integration/      # M09 — Iframes outils doctoraux
│   │   │   ├── AI/               # M10 — Chatbot Ollama
│   │   │   ├── Notification/     # M11 — Emails + Newsletter
│   │   │   ├── Search/           # M12 — Recherche full-text
│   │   │   └── Audit/            # M13 — Logs immuables
│   │   └── ...
│   ├── config/
│   │   └── database.php          # Connexion PostgreSQL configurée
│   ├── .env                      # Variables d'environnement
│   └── composer.json
│
├── docker/
│   ├── php/
│   │   ├── Dockerfile            # PHP 8.3-FPM + extensions PostgreSQL
│   │   └── php.ini
│   ├── nginx/
│   │   └── conf.d/app.conf
│   └── postgres/
│       └── init.sql              # Initialisation DB de test
│
├── ummisco_database.sql          # ← Schéma PostgreSQL complet (importé automatiquement)
├── docker-compose.yml
├── start.ps1                     # Script de démarrage Windows
└── README.md
```

---

## 🔐 Règles métier importantes

| Règle | Description |
|---|---|
| **RG-009** | Doctorants → workflow de validation OBLIGATOIRE |
| **RG-011** | Validateur = admin de l'axe concerné uniquement |
| **RG-012** | Chercheurs → publication directe autorisée |
| **RG-018** | Toute modification ACL tracée dans audit_logs |
| **RG-022** | Conversations chatbot non persistées (session only) |
| **RG-007** | Tout dataset doit avoir une licence avant publication |
| **RG-003** | 5 tentatives de login → verrouillage 15 min (Keycloak) |

---

## 🤝 Équipe & Conventions

Voir [docs/conventions.md](docs/conventions.md) pour les conventions de code.  
Voir [docs/ADR/](docs/ADR/) pour les décisions d'architecture.

**Stack complète :** Laravel 11 · Vue.js 3 · Inertia.js · Tailwind CSS 3 · PostgreSQL 16 · Redis 7 · Keycloak 24 · MinIO · Ollama (LLaMA 3) · Docker · GitHub Actions
