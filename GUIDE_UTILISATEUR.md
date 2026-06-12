# 📖 Guide d'Utilisation — Portail UMMISCO

Bienvenue sur le guide d'utilisation du portail institutionnel de l'UMMISCO. Ce document est destiné aux utilisateurs de l'application pour les guider dans les opérations courantes.

---

## 1. Rôles et Profils Utilisateurs

L'accès aux différentes sections dépend de votre profil :
- **Visiteur** : Peut consulter publiquement les articles, les axes de recherche et les publications agrégées depuis des bases de données externes.
- **Chercheur** : Possède un espace personnel (Dashboard). Il peut publier des articles et des jeux de données de façon directe.
- **Doctorant** : Possède un espace personnel, mais ses publications doivent obligatoirement passer par un **processus de validation** avant d'être publiques (règle institutionnelle).
- **Responsable d'Axe (axe_admin)** : Peut valider ou rejeter les soumissions des doctorants rattachés à son axe de recherche.
- **Super Administrateur** : A accès au panneau d'administration, à la gestion documentaire (génération de PDF), à la configuration des imports scientifiques et aux statistiques globales.

---

## 2. Le Portail Public (Visiteurs)

Accessible à l'URL racine (`/`), le portail public est la vitrine de l'UMMISCO.

### 2.1 Rechercher une publication
1. Naviguez dans l'onglet **Publications**.
2. Vous pouvez filtrer les travaux réalisés en interne par type, par année ou par axe thématique.
3. La barre de recherche intègre un moteur "Full-Text" très performant pour scanner le contenu exact des résumés.

### 2.2 Consulter des données externes (Monde Scientifique)
1. Allez dans l'onglet **Articles externes**.
2. Vous avez ici accès à une base de données agrégée comprenant des millions de publications issues de sources reconnues : **Semantic Scholar, OpenAlex, arXiv et CrossRef**.
3. Si un PDF gratuit est disponible, un bouton "Télécharger le PDF" apparaîtra (via l'intégration d'Unpaywall).

---

## 3. L'Espace Connecté (Chercheurs et Doctorants)

Accessible après connexion, depuis l'URL (`/dashboard`).

### 3.1 Soumettre une nouvelle publication (Doctorants)
1. Dans le menu latéral, cliquez sur **Mes publications** puis sur le bouton **Nouvelle publication**.
2. Remplissez le formulaire de soumission (titre, résumé, fichier PDF, mots-clés, axe de rattachement).
3. Une fois soumis, l'état de l'article passe "En attente". Un email ou une notification est envoyé au responsable de votre axe.
4. Si le responsable demande des modifications, l'article vous sera renvoyé. Vous devrez modifier le contenu et soumettre de nouveau.

### 3.2 Publier directement (Chercheurs)
Les chercheurs de l'UMMISCO sont exemptés du workflow de validation. Une fois que vous remplissez et soumettez le même formulaire, votre contenu est immédiatement publié et visible de tous.

### 3.3 Partager un Dataset
1. Allez dans **Mes datasets** > **Nouveau dataset**.
2. Remplissez la description. Vous devrez obligatoirement choisir une licence (ex: *Creative Commons CC BY 4.0*) avant de pouvoir uploader votre fichier CSV, JSON ou Excel.
3. Le fichier sera stocké de manière sécurisée et mis à la disposition du public.

---

## 4. L'Espace Administration (Responsables d'axe)

### 4.1 Valider les soumissions (Workflow)
1. Dans la barre latérale du dashboard, cliquez sur le menu **Soumissions** (un badge rouge indique le nombre d'articles en attente).
2. Pour chaque article, vous pouvez consulter le contenu (titre, résumé, téléchargement du fichier joint).
3. Vous disposez de deux actions :
   - **Approuver** : L'article devient immédiatement public.
   - **Rejeter (Demande de révision)** : Vous devez impérativement saisir un motif clair expliquant pourquoi la publication est renvoyée au doctorant.

---

## 5. Le Back-Office Global (Super Administrateur)

L'interface est accessible depuis la route sécurisée `/admin` ou via l'icône de la barre de navigation.

### 5.1 Génération de PDF Administratifs (Gestion documentaire)
1. Allez dans **Documents admin.**
2. Vous y trouverez des formulaires dynamiques (ex: *Bon de commande d'achat*, *Prestation de service*, *Convention de stage*).
3. Remplissez le formulaire directement sur le site. Les totaux (TVA, TTC) se calculent automatiquement.
4. Cliquez sur **Générer le PDF**. Un document officiel, reprenant fidèlement les chartes de l'UMMISCO, de l'IRD et de l'UCAD, sera téléchargé sur votre machine.

### 5.2 Supervision des imports scientifiques
Bien que l'import d'articles depuis OpenAlex, CrossRef, etc. soit exécuté automatiquement chaque nuit à 02h00, vous pouvez le piloter manuellement.
1. Allez dans **Import scientifique**.
2. Vous visualiserez les statistiques des bases de données.
3. Le bouton **"Lancer la synchronisation"** permet de forcer une récupération immédiate sur un mot clé spécifique.

---

### Une question ou un problème ?
> L'ensemble des événements (soumissions, validations, connexions) est suivi dans un journal d'audit sécurisé afin de garantir la traçabilité des opérations. Si vous rencontrez un comportement inattendu, veuillez contacter l'administrateur technique du laboratoire.
