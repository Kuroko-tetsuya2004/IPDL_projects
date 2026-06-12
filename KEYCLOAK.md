# 🔐 Configuration Keycloak — Guide complet A à Z

> **Prérequis** : L'application tourne déjà en mode Mock (`KEYCLOAK_MOCK=true`).
> Ce guide te fait passer de zéro à une authentification Keycloak fonctionnelle.

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

## Dépannage

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
