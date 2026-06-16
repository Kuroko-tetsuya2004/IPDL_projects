# Guide Utilisateur Complet — Portail Web UMMISCO

Bienvenue sur le portail scientifique UMMISCO ! Ce guide s'adresse à toute personne découvrant l'application, qu'il s'agisse d'un simple visiteur, d'un chercheur, d'un responsable d'équipe ou de l'administrateur système. Il vous explique pas à pas comment naviguer et tirer profit de la plateforme.

---

## 1. Le Portail Public (Accessible à tous)
Dès votre arrivée sur le site (`/`), vous accédez à l'espace public (sans avoir besoin de compte). Il sert de vitrine au laboratoire.

* **Accueil** : Présentation générale du laboratoire, derniers chiffres et actualités.
* **Publications** : Le moteur de recherche principal. Vous y trouverez tous les articles, thèses et rapports validés par le laboratoire. Vous pouvez chercher par mot-clé, filtrer par année ou par type de document.
* **Données (Datasets)** : Le catalogue dédié exclusivement aux jeux de données produits par le laboratoire.
* **Axes** : Liste des équipes (Axes de recherche), présentant les responsables et les membres affiliés.
* **Projets** : Vitrine des projets de recherche en cours.

> **Astuce Recherche** : Si une publication ou un dataset public n'est pas hébergé localement, le système cherchera en temps réel sur les bases de données mondiales (OpenAlex / DataCite) pour vous fournir l'information !

---

## 2. Connexion et Profils
Pour publier des travaux ou gérer le site, cliquez sur **Connexion** (en haut à droite). 
Selon les permissions qui vous ont été accordées par l'administrateur, vous disposerez d'un des 4 rôles suivants :

1. **Visiteur** : Un compte simple (limité à la consultation).
2. **Chercheur / Doctorant** : Permet de soumettre, importer et gérer ses propres travaux scientifiques. *Note : les travaux des doctorants sont soumis à validation.*
3. **Responsable d'Axe** : Autorise la modération (validation ou rejet) des travaux soumis par les membres de l'axe.
4. **Super Administrateur** : Contrôle absolu sur la plateforme, les utilisateurs et les paramètres.

---

## 3. Espace Chercheur (Votre Tableau de Bord)
Si vous êtes membre du laboratoire, vous accédez après connexion à votre espace privé.

### 3.1. Mon Profil (La base)
* Allez dans **Paramètres > Profil**.
* Renseignez vos informations (Axe de recherche, spécialité).
* **Action très recommandée** : Ajoutez votre **Identifiant ORCID** (ex: `0000-0002-1234-5678`). C'est la clé qui permettra au portail d'automatiser tout votre travail !

### 3.2. Gérer ses Travaux ("Mes Publications" et "Mes Datasets")
Ces deux menus regroupent l'ensemble de votre production. Vos documents peuvent avoir trois statuts :
* 📝 *Brouillon* : Vous travaillez encore dessus.
* ⏳ *En attente* : Le document a été envoyé au responsable de votre axe pour validation.
* ✅ *Publié* : Le document a été validé et est désormais visible de tous sur le portail public.

### 3.3. Comment ajouter des travaux ? (3 Méthodes simples)
Fini la saisie manuelle interminable ! Le portail vous propose 3 façons d'ajouter vos travaux :

1. **✨ La Synchronisation Automatique (ORCID)** 
   * Si vous avez renseigné votre ORCID dans votre profil, allez dans *Mes Publications* ou *Mes Datasets* et cliquez sur **Synchroniser**.
   * Le portail va fouiller le web mondial (OpenAlex pour les articles, DataCite pour les données) et importera automatiquement tout ce que vous avez publié récemment.
2. **🔍 L'Import par DOI**
   * Vous avez le DOI d'un de vos jeux de données (ex: `10.5061/dryad.cv0k0`) ? Collez-le dans la barre d'importation dans l'onglet *Mes Datasets*. Le système récupèrera l'intégralité du dataset pour vous !
3. **✍️ La Saisie Manuelle**
   * Pour les travaux non publiés ailleurs, vous pouvez toujours soumettre un document en remplissant un formulaire classique (titre, résumé, fichier PDF, etc.).

---

## 4. Espace Responsable d'Axe (Le Validateur)
En tant que chef d'équipe, un menu spécial apparaît dans votre barre latérale : **Soumissions**.
C'est ici que tombent les travaux déposés manuellement par les doctorants de votre axe.

* Pour chaque document, vous pouvez consulter ses détails (ou son fichier PDF).
* Vous pouvez cliquer sur **Approuver** : le document bascule instantanément sur le site public.
* Vous pouvez cliquer sur **Rejeter** : le document retourne à son auteur, et vous devez laisser un motif clair expliquant ce qui doit être corrigé.

---

## 5. Espace Super Administrateur (La Tour de Contrôle)
L'administrateur gère l'infrastructure via le panneau d'administration :

* **Utilisateurs et Accès** : Permet de bloquer des comptes ou de changer les privilèges (ex: nommer un nouveau responsable d'axe).
* **Documents admin** : Génération de PDF Administratifs (bons de commande, attestations de stage) avec calculs automatiques, exportables directement.
* **Publications Globales** : Permet de voir et de modifier toutes les publications de la plateforme, quel que soit l'auteur.
* **Journaux d'Audit (Sécurité)** : C'est le carnet de bord intouchable du système. Il liste "Qui a fait quoi et quand" (ex: connexion, modification de rôle, validation de publication). Utile en cas d'erreur ou de litige.

---

## 6. La Cloche de Notifications 🔔
Gardez un œil sur l'icône de la cloche en haut à droite ! Elle vous alerte en temps réel :
* Lorsqu'une de vos publications vient d'être validée (ou rejetée).
* Lorsqu'un de vos imports ORCID est terminé avec le bilan des données récupérées.
* Lorsqu'une nouvelle tâche vous attend (pour les responsables d'axe).

> *En cas de comportement inattendu, n'hésitez pas à vous rapprocher de l'administrateur technique. L'ensemble des événements étant tracé, le support sera très rapide.*
