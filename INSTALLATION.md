# ✅ Installation — Ce qu'il reste à faire

> Les étapes déjà effectuées (clone, `.env`, Composer) sont terminées.
> Il ne reste que les étapes Docker ci-dessous.

---

## Étape 1 — Démarrer Docker Desktop

Lance **Docker Desktop** depuis le menu Démarrer et attends que l'icône dans la barre des tâches soit verte (« Engine running »).

Vérifie ensuite que Docker répond :

```powershell
docker --version
```

---

## Étape 2 — Lancer l'infrastructure

> ⚠️ **Important** : exécute ces commandes depuis le dossier du projet, pas depuis `System32`.

2. Démarrez les conteneurs en tâche de fond :
```bash
docker compose up -d
```

Vérifie que tous les conteneurs tournent :

```powershell
docker compose ps
```

Tous les services doivent être en état `Up` ou `healthy` :

| Service | Port local |
|---------|-----------|
| Nginx (accès web) | http://localhost:8080 |
| App (PHP-FPM) | interne |
| PostgreSQL 16 | 5432 |
| Redis 7 | 6379 |

---

## Étape 3 — Générer la clé d'application

```powershell
docker compose exec app php artisan key:generate
```

---

## Étape 4 — Vérifier la base de données

Le schéma SQL est importé automatiquement au premier démarrage. Vérifie qu'il y a bien ~35 tables :

```powershell
docker compose exec postgres psql -U ummisco_user -d ummisco_app -c "\dt"
```

---

## Étape 5 — Ouvrir l'application 🎉

**http://localhost:8080**

Pour se connecter (mode mock, sans Keycloak) :
1. Cliquez **« Connexion »**
2. Choisissez un rôle (ex. **Super Administrateur**)
3. Cliquez **« Se connecter »**

---

## En cas de problème

```powershell
# Voir les logs de l'app
docker compose logs -f app

# Erreur 500 → vider les caches
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

# Port 8080 occupé → modifier dans docker-compose.yml
#   ports: ["8090:80"]
```
