# 🔐 Guide Complet — Lier Keycloak au Projet Portail UMMISCO sur Railway

Ce guide décrit étape par étape comment configurer et lier Keycloak à votre application Laravel en environnement de production sur **Railway**.

---

## 🗺️ Schéma des URLs de Production

Pour que l'authentification OpenID Connect (OIDC) fonctionne, vous devez disposer de deux adresses HTTPS distinctes générées ou configurées sur Railway :

1.  **URL de l'Application Laravel** (ex. `https://portail.up.railway.app`)
2.  **URL de Keycloak** (ex. `https://keycloak.up.railway.app`)

---

## 🛠️ ÉTAPE 1 — Déploiement et Variables de Keycloak sur Railway

Lorsque vous lancez le service Keycloak sur Railway (via le fichier `docker-compose.prod.yml` ou en créant un service Keycloak autonome), vous devez impérativement configurer ces variables d'environnement dans le tableau de bord de Railway pour activer le support du reverse proxy HTTPS (sinon vous obtiendrez des erreurs d'URL mixtes HTTP/HTTPS ou des boucles de redirection) :

### Variables du service Keycloak sur Railway :

| Variable | Valeur | Description |
| :--- | :--- | :--- |
| `KC_PROXY_HEADERS` | `xforwarded` | **CRITIQUE** : Indique à Keycloak qu'il est derrière le proxy SSL de Railway |
| `KC_HOSTNAME` | `keycloak.up.railway.app` | Votre nom de domaine public Keycloak sur Railway |
| `KC_HTTP_ENABLED` | `true` | Autorise Keycloak à recevoir le trafic HTTP interne du proxy |
| `KEYCLOAK_ADMIN` | `admin` | Identifiant administrateur console |
| `KEYCLOAK_ADMIN_PASSWORD` | `votre_mot_de_passe_admin` | Mot de passe administrateur console |

---

## ⚙️ ÉTAPE 2 — Configuration de la console Keycloak (HTTPS)

1.  Connectez-vous à la console d'administration sur votre URL publique Keycloak (ex: `https://keycloak.up.railway.app`).
2.  Créez (ou importez) le Realm nommé **`ummisco`**.
3.  Sélectionnez le Realm `ummisco` dans le menu en haut à gauche.
4.  Allez dans **Clients** ➔ **Create Client** et configurez le client ainsi :

### General Settings
*   **Client type** : `OpenID Connect`
*   **Client ID** : `laravel-app`
*   **Name** : `Portail UMMISCO`

### Capability Config
*   **Client authentication** : ✅ **On** (Requis pour obtenir le Client Secret)
*   **Authorization** : ❌ Off
*   **Standard flow** : ✅ **On**
*   **Direct access grants** : ✅ **On**

### Login Settings (CRITIQUE : Mettre des URLs HTTPS réelles)
Remplacez `https://portail.up.railway.app` par l'URL publique générée pour votre service Laravel sur Railway :

*   **Root URL** : `https://portail.up.railway.app`
*   **Home URL** : `https://portail.up.railway.app`
*   **Valid redirect URIs** : `https://portail.up.railway.app/auth/callback`
*   **Valid post logout redirect URIs** : `https://portail.up.railway.app`
*   **Web origins** : `https://portail.up.railway.app`

Cliquez sur **Save**.

### Récupération du Secret
1.  Dans la configuration de votre client `laravel-app`, allez sur l'onglet **Credentials**.
2.  Copiez la valeur du **Client secret**.

---

## 🌐 ÉTAPE 3 — Lier Laravel à Keycloak sur le Dashboard Railway

Allez dans le service de votre **Application Laravel** sur Railway, ouvrez l'onglet **Variables** et configurez les variables OIDC de production suivantes :

```env
KEYCLOAK_MOCK=false
KEYCLOAK_BASE_URL=https://keycloak.up.railway.app
KEYCLOAK_REALM=ummisco
KEYCLOAK_CLIENT_ID=laravel-app
KEYCLOAK_CLIENT_SECRET=coller_le_client_secret_recupere_a_l_etape_precedente
KEYCLOAK_REDIRECT_URI=https://portail.up.railway.app/auth/callback
SESSION_SECURE_COOKIE=true
```

> ⚠️ **Important** :
> *   `KEYCLOAK_BASE_URL` doit pointer vers l'URL **publique HTTPS** de Keycloak (pas l'URL interne du conteneur), car c'est celle sur laquelle le navigateur de l'utilisateur va être redirigé pour s'authentifier.
> *   `SESSION_SECURE_COOKIE` doit être passé à `true` en production pour que les cookies de session transitent uniquement en HTTPS, ce qui est obligatoire pour éviter le blocage OIDC.

---

## 🛠️ Dépannage des erreurs fréquentes en production

### 1. Erreur « Invalid parameter: redirect_uri »
Cette erreur se produit quand l'URL envoyée par l'application Laravel ne correspond pas exactement à celle enregistrée dans la console Keycloak.
*   **Vérification 1** : Dans Keycloak, vérifiez que **Valid redirect URIs** vaut exactement `https://portail.up.railway.app/auth/callback` (avec `https` et non `http`).
*   **Vérification 2** : Dans les variables Railway de Laravel, vérifiez que `KEYCLOAK_REDIRECT_URI` est identique et utilise bien le protocole `https`.

### 2. Boucle de redirection infinie (HTTP vers HTTPS)
Si l'application n'arrive pas à détecter qu'elle tourne sous HTTPS à cause du proxy de Railway :
*   Dans votre code Laravel, assurez-vous que le middleware des proxys de confiance (`TrustProxies`) fait confiance au proxy de Railway. (Sous Laravel 11, cela est géré automatiquement de manière sécurisée en production).
