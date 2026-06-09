const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  HeadingLevel, AlignmentType, BorderStyle, WidthType, ShadingType,
  LevelFormat, PageNumber, PageBreak, Header, Footer, Tab, TabStopType,
  TabStopPosition
} = require('docx');
const fs = require('fs');

// ─── Helpers ────────────────────────────────────────────────────────────────

const BORDER = { style: BorderStyle.SINGLE, size: 1, color: "CCCCCC" };
const BORDERS = { top: BORDER, bottom: BORDER, left: BORDER, right: BORDER };
const CELL_MARGINS = { top: 80, bottom: 80, left: 120, right: 120 };

const COLOR = {
  primary: "1F4E79",
  secondary: "2E75B6",
  accent: "2E86C1",
  light: "D6E4F0",
  header: "1F4E79",
  headerText: "FFFFFF",
  altRow: "EBF5FB",
  white: "FFFFFF",
};

// Page dims: A4, 1" margins => content width = 11906 - 2*1440 = 9026 DXA
const CONTENT_WIDTH = 9026;

function h(level, text, options = {}) {
  const levels = [HeadingLevel.HEADING_1, HeadingLevel.HEADING_2, HeadingLevel.HEADING_3];
  return new Paragraph({
    heading: levels[level - 1],
    children: [new TextRun({ text, bold: true })],
    spacing: { before: level === 1 ? 360 : 240, after: 120 },
    ...options
  });
}

function p(text, options = {}) {
  return new Paragraph({
    children: [new TextRun({ text, size: 22 })],
    spacing: { before: 60, after: 60 },
    ...options
  });
}

function bold(text) { return new TextRun({ text, bold: true, size: 22 }); }
function run(text) { return new TextRun({ text, size: 22 }); }

function bullet(text, level = 0) {
  return new Paragraph({
    numbering: { reference: "bullets", level },
    children: [new TextRun({ text, size: 22 })],
    spacing: { before: 40, after: 40 }
  });
}

function numbered(text, level = 0) {
  return new Paragraph({
    numbering: { reference: "numbers", level },
    children: [new TextRun({ text, size: 22 })],
    spacing: { before: 40, after: 40 }
  });
}

function mkRow(cells, isHeader = false, altBg = false) {
  return new TableRow({
    tableHeader: isHeader,
    children: cells.map((cell, i) => {
      const fill = isHeader ? COLOR.header : (altBg ? COLOR.altRow : COLOR.white);
      const textColor = isHeader ? COLOR.headerText : "000000";
      const content = typeof cell === 'string' ? cell : cell.text;
      const isBold = isHeader || (typeof cell === 'object' && cell.bold);
      return new TableCell({
        borders: BORDERS,
        width: { size: typeof cell === 'object' && cell.width ? cell.width : undefined, type: WidthType.DXA },
        shading: { fill, type: ShadingType.CLEAR },
        margins: CELL_MARGINS,
        children: [new Paragraph({
          children: [new TextRun({ text: content, bold: isBold, size: 20, color: textColor })]
        })]
      });
    })
  });
}

function table(headers, rows, colWidths) {
  const total = colWidths.reduce((a, b) => a + b, 0);
  const headerRow = new TableRow({
    tableHeader: true,
    children: headers.map((h, i) => new TableCell({
      borders: BORDERS,
      width: { size: colWidths[i], type: WidthType.DXA },
      shading: { fill: COLOR.header, type: ShadingType.CLEAR },
      margins: CELL_MARGINS,
      children: [new Paragraph({
        children: [new TextRun({ text: h, bold: true, size: 20, color: COLOR.headerText })]
      })]
    }))
  });
  const dataRows = rows.map((row, ri) => new TableRow({
    children: row.map((cell, ci) => new TableCell({
      borders: BORDERS,
      width: { size: colWidths[ci], type: WidthType.DXA },
      shading: { fill: ri % 2 === 0 ? COLOR.white : COLOR.altRow, type: ShadingType.CLEAR },
      margins: CELL_MARGINS,
      children: [new Paragraph({
        children: [new TextRun({ text: typeof cell === 'object' ? cell.text : cell, bold: typeof cell === 'object' && cell.bold, size: 19 })]
      })]
    }))
  }));
  return new Table({ width: { size: total, type: WidthType.DXA }, columnWidths: colWidths, rows: [headerRow, ...dataRows] });
}

function divider() {
  return new Paragraph({
    border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: COLOR.secondary, space: 1 } },
    spacing: { before: 120, after: 120 },
    children: []
  });
}

function pageBreak() {
  return new Paragraph({ children: [new TextRun({ break: 1 })] });
}

function note(text) {
  return new Paragraph({
    children: [new TextRun({ text: `ℹ️  ${text}`, size: 20, italics: true, color: "555555" })],
    spacing: { before: 80, after: 80 },
    indent: { left: 360 }
  });
}

// ─── DOCUMENT CONTENT ───────────────────────────────────────────────────────

const children = [

  // COVER PAGE
  new Paragraph({ children: [], spacing: { before: 1440, after: 0 } }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    children: [new TextRun({ text: "UMMISCO", size: 72, bold: true, color: COLOR.primary })],
    spacing: { before: 0, after: 120 }
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    children: [new TextRun({ text: "Portail Web de Laboratoire", size: 48, bold: true, color: COLOR.secondary })],
    spacing: { before: 0, after: 240 }
  }),
  divider(),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    children: [new TextRun({ text: "Dossier de Conception Technique", size: 32, color: "444444", italics: true })],
    spacing: { before: 240, after: 120 }
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    children: [new TextRun({ text: "Analyse complète — Phases 1 à 6", size: 26, color: "666666" })],
    spacing: { before: 0, after: 720 }
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    children: [new TextRun({ text: `Version 1.0 — ${new Date().toLocaleDateString('fr-FR', {year:'numeric',month:'long',day:'numeric'})}`, size: 22, color: "888888" })],
  }),
  pageBreak(),

  // ══════════════════════════════════════════════
  // PHASE 1 — COMPRÉHENSION DU PROBLÈME
  // ══════════════════════════════════════════════
  new Paragraph({
    children: [new TextRun({ text: "PHASE 1 — COMPRÉHENSION DU PROBLÈME", size: 28, bold: true, color: COLOR.headerText })],
    shading: { fill: COLOR.header, type: ShadingType.CLEAR },
    spacing: { before: 240, after: 240 },
    indent: { left: 240, right: 240 }
  }),

  h(1, "1. Contexte & Problématique"),
  h(2, "Domaine métier et contexte organisationnel"),
  p("L'UMMISCO (Unité Mixte Internationale de Modélisation Mathématique et Informatique des Systèmes Complexes) est un laboratoire de recherche conjoint CNRS/IRD/UCAD basé à Dakar, Sénégal. Le laboratoire conduit des recherches pluridisciplinaires couvrant notamment l'épidémiologie, la modélisation climatique, les FabLabs et les objets connectés (IoT). La production scientifique, les outils doctoraux et les données de terrain restent actuellement dispersés, sans vitrine numérique unifiée."),
  p("Le projet vise à créer un portail web institutionnel centralisé permettant de valoriser la production scientifique, de structurer les flux de publication, de faciliter la collaboration avec les partenaires et les bailleurs, et de proposer une expérience utilisateur différenciée selon le profil (visiteur, chercheur, doctorant, partenaire, administrateur)."),

  h(2, "Valeur apportée"),
  bullet("Pour les chercheurs : vitrine personnelle et de leurs travaux, publication directe sans friction"),
  bullet("Pour les doctorants : accès centralisé aux outils de simulation et soumission encadrée"),
  bullet("Pour les partenaires et bailleurs : accès authentifié aux données du laboratoire, meilleure visibilité sur le dynamisme de l'unité"),
  bullet("Pour le grand public : accès libre aux résultats et datasets ouverts"),
  bullet("Pour l'administration : gestion fine des accès, des contenus et des workflows de validation"),

  h(2, "Périmètre du projet"),
  h(3, "IN-SCOPE"),
  bullet("Portail public multilingue (FR/EN) avec pages institutionnelles"),
  bullet("Authentification et gestion des identités via Keycloak"),
  bullet("Gestion des actualités, événements, publications, projets et personnels"),
  bullet("Portails thématiques (épidémiologie, climat, FabLab…)"),
  bullet("Catalogue de datasets avec métadonnées, licences et gestion des accès"),
  bullet("Espace documentaire (rapports, thèses, présentations)"),
  bullet("Formulaires : contact, collaboration, soumission d'articles, newsletter"),
  bullet("Intégration d'outils doctoraux via iframes (Evelop, outil carbone, capteurs cardiaques pour arbres)"),
  bullet("Chatbot IA (LLM) pour l'assistance aux utilisateurs"),
  bullet("Workflow de validation : soumission → révision → publication"),
  bullet("Back-office d'administration sécurisé"),
  bullet("ACL (Access Control List) avec rôles différenciés"),

  h(3, "OUT-OF-SCOPE"),
  bullet("Développement des outils doctoraux eux-mêmes (Evelop, outil carbone, capteurs) — ils sont intégrés via iframe"),
  bullet("Infrastructure de stockage de données de santé (nécessite consultation juridique préalable)"),
  bullet("Portail partenaire différencié pour les médias (point ouvert, hors périmètre v1)"),
  bullet("Application mobile native"),
  bullet("Système de gestion financière ou comptable interne"),
  bullet("Gestion de la paie ou des ressources humaines"),

  h(2, "Hypothèses et présupposés"),
  bullet("L'hébergement principal sera sur l'infrastructure UCAD/UMMISCO, avec Vercel comme fallback éventuel"),
  bullet("Le chatbot IA s'appuiera sur Ollama (modèle local) par préférence de souveraineté des données"),
  bullet("Les outils doctoraux exposent des URLs stables accessibles en HTTPS sans restriction CORS"),
  bullet("Le français est la langue par défaut, avec bascule dynamique vers l'anglais"),
  bullet("Keycloak est retenu pour l'authentification (décision actée)"),
  bullet("Le stack applicatif (PHP/Laravel vs Java/Spring Boot) est encore en cours d'arbitrage"),
  bullet("Les règles RGPD sénégalaises applicables aux données de santé seront précisées avant implémentation"),

  pageBreak(),
  h(1, "2. Parties Prenantes (Stakeholders)"),
  p("Le tableau suivant identifie l'ensemble des parties prenantes du projet, leur rôle et leur niveau d'influence sur les décisions architecturales et fonctionnelles."),
  new Paragraph({ children: [], spacing: { before: 120 } }),
  table(
    ["Partie prenante", "Rôle", "Intérêt dans le projet", "Influence"],
    [
      ["Direction UMMISCO", "Commanditaire", "Valorisation de l'unité, financement et visibilité", "Élevée"],
      ["Chercheurs", "Utilisateurs principaux", "Vitrine personnelle, publication directe, collaborations", "Élevée"],
      ["Étudiants & Doctorants", "Utilisateurs principaux", "Soumission de travaux, accès aux outils de simulation", "Moyenne"],
      ["Administrateurs", "Gestionnaires du système", "Contrôle des accès, validation des contenus, ACL par axe", "Élevée"],
      ["Visiteurs / Grand public", "Utilisateurs finaux", "Consultation des publications et datasets libres", "Faible"],
      ["Partenaires", "Utilisateurs authentifiés", "Accès aux données protégées de leur domaine", "Moyenne"],
      ["Bailleurs de fonds", "Parties prenantes externes", "Visibilité sur le dynamisme du laboratoire", "Moyenne"],
      ["Médias", "Utilisateurs externes", "Accès à l'information institutionnelle (non différencié v1)", "Faible"],
      ["Équipe technique (dev)", "Réalisateurs", "Faisabilité, maintenance, cohérence architecturale", "Élevée"],
      ["Prestataires IA (Ollama)", "Fournisseur externe", "Fourniture du modèle LLM pour le chatbot", "Faible"],
      ["UCAD / DSI", "Hébergeur", "Infrastructure réseau, disponibilité, SLA", "Moyenne"],
      ["CNRS / IRD", "Tutelles", "Conformité institutionnelle, co-branding", "Moyenne"],
    ],
    [2000, 2000, 3200, 1826]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "3. Glossaire Métier"),
  p("Les termes suivants sont utilisés avec un sens précis dans le contexte du projet UMMISCO."),
  new Paragraph({ children: [], spacing: { before: 120 } }),
  table(
    ["Terme", "Définition dans le contexte du projet"],
    [
      ["UMMISCO", "Unité Mixte Internationale de Modélisation Mathématique et Informatique des Systèmes Complexes — laboratoire porteur du projet"],
      ["Portail", "Application web institutionnelle unifiée constituant le point d'entrée numérique du laboratoire"],
      ["Axe thématique", "Regroupement de chercheurs et de projets autour d'une discipline (épidémiologie, climat, FabLab, etc.)"],
      ["Chercheur", "Personnel de recherche permanent ou associé, habilité à publier directement sans validation"],
      ["Doctorant", "Étudiant inscrit en thèse dont les soumissions sont soumises à validation avant publication"],
      ["Publication", "Tout contenu scientifique (article, rapport, thèse, présentation) mis en ligne sur le portail"],
      ["Dataset", "Jeu de données structuré associé à une ou plusieurs publications, avec métadonnées et licence"],
      ["Workflow de validation", "Circuit de relecture et d'approbation des contenus soumis par les doctorants avant mise en ligne"],
      ["ACL", "Access Control List — modèle de gestion granulaire des permissions attribuées à des utilisateurs ou rôles"],
      ["Keycloak", "Serveur d'identité open-source gérant l'authentification SSO, les rôles et les scopes du portail"],
      ["Back-office", "Interface d'administration sécurisée, séparée logiquement et physiquement du portail public"],
      ["Front-office", "Portail public accessible sans authentification aux visiteurs"],
      ["Iframe", "Élément HTML permettant d'intégrer un outil externe dans une page du portail sans développement côté portail"],
      ["Outil doctoral", "Application spécialisée développée par ou pour des doctorants (Evelop, outil carbone, capteurs arbres) intégrée via iframe"],
      ["Chatbot IA", "Assistant conversationnel alimenté par un LLM (Ollama) et embarqué dans le portail pour répondre aux questions des utilisateurs"],
      ["Portail thématique", "Section du portail dédiée à un axe de recherche, avec contenu et accès spécifiques"],
      ["Partenaire", "Organisation extérieure ayant un accord formel avec UMMISCO et bénéficiant d'un accès authentifié à des données protégées"],
      ["Bailleur de fonds", "Organisme finançant des projets de recherche, consulte les indicateurs d'activité du laboratoire"],
      ["Licence (dataset)", "Termes légaux encadrant la réutilisation d'un jeu de données (CC-BY, CC-BY-NC, données propriétaires, etc.)"],
      ["Newsletter", "Bulletin d'information périodique envoyé aux abonnés inscrits depuis le portail"],
      ["Vitrine personnelle", "Page de profil public d'un chercheur présentant ses travaux, publications et affiliations"],
      ["Convention de stage", "Document administratif généré par le système pour encadrer l'accueil d'un stagiaire"],
    ],
    [2500, 6526]
  ),

  pageBreak(),
  // ══════════════════════════════════════════════
  // PHASE 2 — ANALYSE DES BESOINS
  // ══════════════════════════════════════════════
  new Paragraph({
    children: [new TextRun({ text: "PHASE 2 — ANALYSE DES BESOINS", size: 28, bold: true, color: COLOR.headerText })],
    shading: { fill: COLOR.header, type: ShadingType.CLEAR },
    spacing: { before: 240, after: 240 },
    indent: { left: 240, right: 240 }
  }),

  h(1, "4. Besoins Fonctionnels"),
  p("Les user stories sont groupées par module fonctionnel et priorisées selon la méthode MoSCoW."),

  h(2, "Module AUTH — Authentification"),
  table(
    ["ID", "User Story", "Priorité"],
    [
      ["US-AUTH-01", "En tant que tout utilisateur, je veux m'authentifier avec mes identifiants afin d'accéder aux espaces sécurisés.", "Must"],
      ["US-AUTH-02", "En tant qu'administrateur, je veux gérer les rôles et les accès via Keycloak afin de contrôler finement les permissions.", "Must"],
      ["US-AUTH-03", "En tant qu'utilisateur, je veux réinitialiser mon mot de passe afin de récupérer l'accès à mon compte.", "Must"],
      ["US-AUTH-04", "En tant qu'administrateur, je veux activer/désactiver un compte utilisateur afin de gérer les accès.", "Must"],
    ],
    [1200, 6000, 1826]
  ),

  new Paragraph({ children: [], spacing: { before: 160 } }),
  h(2, "Module PUBLIC — Portail Public"),
  table(
    ["ID", "User Story", "Priorité"],
    [
      ["US-PUB-01", "En tant que visiteur, je veux consulter les pages institutionnelles afin de découvrir le laboratoire.", "Must"],
      ["US-PUB-02", "En tant que visiteur, je veux consulter les publications et datasets libres afin d'accéder aux résultats de recherche.", "Must"],
      ["US-PUB-03", "En tant que visiteur, je veux changer la langue (FR/EN) afin de lire le contenu dans ma langue.", "Should"],
      ["US-PUB-04", "En tant que visiteur, je veux m'inscrire à la newsletter afin de suivre l'actualité du laboratoire.", "Should"],
      ["US-PUB-05", "En tant que visiteur, je veux contacter le laboratoire via un formulaire afin de poser une question.", "Should"],
      ["US-PUB-06", "En tant que visiteur, je veux interagir avec le chatbot IA afin d'obtenir une assistance rapide.", "Could"],
    ],
    [1200, 6000, 1826]
  ),

  new Paragraph({ children: [], spacing: { before: 160 } }),
  h(2, "Module CONTENT — Gestion des Contenus"),
  table(
    ["ID", "User Story", "Priorité"],
    [
      ["US-CNT-01", "En tant que chercheur, je veux publier directement un article/actualité afin de diffuser mes travaux sans délai.", "Must"],
      ["US-CNT-02", "En tant que doctorant, je veux soumettre un contenu à validation afin que mes travaux soient revus avant publication.", "Must"],
      ["US-CNT-03", "En tant qu'administrateur, je veux valider ou rejeter un contenu soumis afin de contrôler la qualité des publications.", "Must"],
      ["US-CNT-04", "En tant que chercheur, je veux gérer ma vitrine personnelle afin de présenter mes travaux et affiliations.", "Must"],
      ["US-CNT-05", "En tant que chercheur, je veux gérer les actualités et événements de mon axe afin de tenir la communauté informée.", "Must"],
      ["US-CNT-06", "En tant qu'administrateur, je veux gérer les portails thématiques afin de maintenir la cohérence des axes.", "Should"],
    ],
    [1200, 6000, 1826]
  ),

  new Paragraph({ children: [], spacing: { before: 160 } }),
  h(2, "Module DATASET — Gestion des Données"),
  table(
    ["ID", "User Story", "Priorité"],
    [
      ["US-DAT-01", "En tant que chercheur, je veux déposer un dataset avec ses métadonnées afin de le rendre accessible.", "Must"],
      ["US-DAT-02", "En tant que visiteur/partenaire, je veux consulter le catalogue de datasets afin de trouver des données pertinentes.", "Must"],
      ["US-DAT-03", "En tant qu'administrateur, je veux définir la licence et les droits d'accès d'un dataset afin de contrôler sa diffusion.", "Must"],
      ["US-DAT-04", "En tant que partenaire authentifié, je veux accéder aux datasets protégés de mon domaine afin d'exploiter les données partagées.", "Must"],
    ],
    [1200, 6000, 1826]
  ),

  new Paragraph({ children: [], spacing: { before: 160 } }),
  h(2, "Module USER — Gestion des Profils"),
  table(
    ["ID", "User Story", "Priorité"],
    [
      ["US-USR-01", "En tant qu'utilisateur connecté, je veux gérer mon profil afin de maintenir mes informations à jour.", "Must"],
      ["US-USR-02", "En tant qu'administrateur, je veux gérer les profils membres afin d'administrer l'annuaire du laboratoire.", "Must"],
      ["US-USR-03", "En tant que chercheur/doctorant, je veux contacter un autre membre via le portail afin de collaborer.", "Should"],
      ["US-USR-04", "En tant qu'administrateur, je veux émettre une convention de stage afin de formaliser l'accueil d'un stagiaire.", "Could"],
    ],
    [1200, 6000, 1826]
  ),

  new Paragraph({ children: [], spacing: { before: 160 } }),
  h(2, "Module INTEGRATION — Outils Doctoraux & IA"),
  table(
    ["ID", "User Story", "Priorité"],
    [
      ["US-INT-01", "En tant que doctorant, je veux accéder aux outils de simulation (Evelop, outil carbone) afin de réaliser mes travaux de recherche.", "Should"],
      ["US-INT-02", "En tant qu'administrateur, je veux configurer une iframe d'outil doctoral afin d'intégrer un nouvel outil sans déploiement.", "Should"],
      ["US-INT-03", "En tant qu'utilisateur, je veux utiliser le chatbot IA afin d'obtenir des réponses sur le laboratoire et ses activités.", "Could"],
      ["US-INT-04", "En tant que doctorant, je veux faire une simulation via le portail afin d'explorer des scénarios de recherche.", "Could"],
      ["US-INT-05", "En tant que partenaire, je veux demander une prestation de service afin d'initier une collaboration formelle.", "Won't (v1)"],
      ["US-INT-06", "En tant qu'administrateur, je veux émettre un bon d'achat via le système afin de gérer des acquisitions.", "Won't (v1)"],
    ],
    [1200, 6000, 1826]
  ),

  pageBreak(),
  h(1, "5. Besoins Non-Fonctionnels"),
  table(
    ["Exigence", "Description", "Critère de mesure"],
    [
      ["Performance", "Le portail doit répondre rapidement même sous charge modérée", "Temps de chargement page < 3s pour 95% des requêtes (LCP < 2,5s selon Core Web Vitals)"],
      ["Sécurité", "Protection contre les attaques classiques (XSS, CSRF, injection SQL, IDOR)", "0 vulnérabilité critique dans les rapports OWASP Top 10 ; audit sécurité avant mise en production"],
      ["Disponibilité", "Le portail public doit être accessible en quasi-permanence", "Taux de disponibilité ≥ 99% (hors maintenances planifiées), mesuré sur 30 jours glissants"],
      ["Scalabilité", "Le système doit supporter la croissance du contenu et des utilisateurs", "Architecture horizontalement scalable ; support de 500 utilisateurs simultanés sans dégradation"],
      ["Maintenabilité", "Le code doit être compréhensible et facilement modifiable par l'équipe interne", "Couverture de tests ≥ 70% ; documentation technique à jour ; score de lisibilité Sonar ≥ B"],
      ["Portabilité", "Le système doit pouvoir migrer d'hébergeur sans refonte", "Conteneurisation Docker complète ; aucune dépendance OS-spécifique non documentée"],
      ["Accessibilité", "Le portail public doit être accessible aux personnes handicapées", "Conformité WCAG 2.1 niveau AA ; validation avec outil axe-core"],
      ["Multilingue", "Basculement FR ↔ EN sans rechargement de page", "Traductions complètes pour 100% des contenus statiques ; délai de bascule < 200ms"],
      ["Conformité légale", "Respect du RGPD et de la réglementation sénégalaise sur les données personnelles", "Politique de confidentialité publiée ; consentement cookie ; DPA signé avec prestataires tiers"],
      ["Auditabilité", "Traçabilité des actions sensibles (publication, modification d'accès)", "Journaux d'audit conservés 12 mois ; chaque action horodatée avec auteur identifié"],
    ],
    [2000, 3500, 3526]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "6. Contraintes du Projet"),
  h(2, "Contraintes techniques"),
  bullet("Keycloak imposé pour l'authentification (décision actée)"),
  bullet("Stack applicatif en cours d'arbitrage entre PHP/Laravel et Java/Spring Boot"),
  bullet("Intégration des outils doctoraux uniquement via iframe (contrainte architecturale)"),
  bullet("Support des navigateurs modernes (Chrome, Firefox, Safari, Edge — 2 dernières versions)"),
  bullet("Hébergement préférentiel sur serveurs UCAD/UMMISCO"),

  h(2, "Contraintes organisationnelles"),
  bullet("Capacités de maintenance interne limitées : profils Java/Spring Boot rares en interne"),
  bullet("Budget non communiqué à ce stade — à confirmer avant la phase d'élaboration"),
  bullet("Équipe de développement de taille réduite ; démarche itérative obligatoire"),
  bullet("Livrables intermédiaires attendus par axe de priorité (auth + base de données + portail public en premier)"),

  h(2, "Contraintes légales et réglementaires"),
  bullet("RGPD applicable aux données de ressortissants européens éventuellement collectées"),
  bullet("Réglementation sénégalaise sur les données personnelles (loi n°2008-12 sur la protection des données)"),
  bullet("Données de cardiologie (capteurs arbres) : consultation juridique obligatoire avant hébergement"),
  bullet("Licences des datasets : chaque jeu de données doit avoir une licence explicite"),

  h(2, "Dépendances externes"),
  bullet("Keycloak — serveur d'identité (auto-hébergé sur infrastructure UMMISCO)"),
  bullet("Ollama — service LLM local pour le chatbot IA"),
  bullet("Evelop — outil doctoral externe intégré via iframe"),
  bullet("Outil carbone d'Osman — outil doctoral externe intégré via iframe"),
  bullet("Capteurs cardiaques pour arbres — flux de données doctorales intégré via iframe"),
  bullet("Serveurs UCAD/UMMISCO — infrastructure d'hébergement principale"),
  bullet("Vercel (optionnel) — fallback hébergement en cas d'indisponibilité de l'infrastructure principale"),

  pageBreak(),
  // ══════════════════════════════════════════════
  // PHASE 3 — MODÉLISATION FONCTIONNELLE
  // ══════════════════════════════════════════════
  new Paragraph({
    children: [new TextRun({ text: "PHASE 3 — MODÉLISATION FONCTIONNELLE", size: 28, bold: true, color: COLOR.headerText })],
    shading: { fill: COLOR.header, type: ShadingType.CLEAR },
    spacing: { before: 240, after: 240 },
    indent: { left: 240, right: 240 }
  }),

  h(1, "7. Acteurs du Système"),
  table(
    ["Acteur", "Type", "Description", "Cas d'utilisation associés"],
    [
      ["Visiteur", "Acteur primaire humain", "Internaute non authentifié consultant le portail", "UC-01, UC-16"],
      ["Chercheur", "Acteur primaire humain", "Personnel de recherche autorisé à publier directement", "UC-02, UC-03, UC-05, UC-09, UC-10, UC-11"],
      ["Doctorant", "Acteur primaire humain", "Étudiant en thèse, publication soumise à validation", "UC-02, UC-03, UC-04, UC-10, UC-11, UC-13, UC-14"],
      ["Partenaire", "Acteur primaire humain", "Organisation externe avec accès authentifié aux données protégées", "UC-02, UC-03, UC-11, UC-17"],
      ["Bailleur de fonds", "Acteur primaire humain", "Financeur consultant les indicateurs d'activité", "UC-01, UC-02, UC-03"],
      ["Administrateur", "Acteur primaire humain", "Gestionnaire du système (peut être chercheur d'un axe)", "UC-02, UC-06, UC-07, UC-08, UC-09, UC-15, UC-18"],
      ["Keycloak", "Acteur secondaire système", "Serveur d'identité gérant l'authentification et les rôles", "UC-02"],
      ["Système IA (Ollama)", "Acteur secondaire système", "Moteur LLM pour le chatbot", "UC-12"],
      ["Outils doctoraux", "Acteur secondaire système", "Applications externes intégrées via iframe", "UC-13, UC-14"],
      ["Serveur mail", "Acteur secondaire système", "Service d'envoi des notifications et de la newsletter", "UC-11, UC-04"],
    ],
    [1600, 1600, 3200, 2626]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "8. Cas d'Utilisation Principaux"),

  // UC-01
  h(2, "UC-01 — Consulter le portail public"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Visiteur (non authentifié)"],
      ["Acteurs secondaires", "—"],
      ["Préconditions", "Le portail est accessible en ligne"],
      ["Postconditions", "Le visiteur a consulté les contenus publics"],
      ["Scénario nominal", "1. Le visiteur accède à l'URL du portail\n2. La page d'accueil s'affiche (institution, actualités, portails thématiques)\n3. Le visiteur navigue vers une section (publications, datasets, équipe, agenda)\n4. Le visiteur consulte le contenu souhaité"],
      ["Alt. / Exceptions", "Alt-1 : Le visiteur souhaite un contenu protégé → redirection vers la page de connexion\nEx-1 : Serveur indisponible → page d'erreur 503 avec message de maintenance"],
      ["Règles métier", "RG-001, RG-002"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-02
  h(2, "UC-02 — S'authentifier"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Tout utilisateur (chercheur, doctorant, partenaire, administrateur)"],
      ["Acteurs secondaires", "Keycloak (IdP)"],
      ["Préconditions", "L'utilisateur possède un compte actif sur Keycloak"],
      ["Postconditions", "L'utilisateur est authentifié, un token JWT est émis, les droits correspondant à son rôle sont appliqués"],
      ["Scénario nominal", "1. L'utilisateur clique sur 'Connexion'\n2. Redirection vers la page de login Keycloak\n3. L'utilisateur saisit ses identifiants\n4. Keycloak valide les identifiants et émet un token JWT\n5. Redirection vers le portail avec session active"],
      ["Alt. / Exceptions", "Alt-1 : Connexion SSO avec compte institutionnel (UCAD)\nEx-1 : Identifiants invalides → message d'erreur, tentative comptabilisée\nEx-2 : Compte verrouillé → message dédié, contact admin proposé\nEx-3 : Token expiré → re-authentification silencieuse via refresh token"],
      ["Règles métier", "RG-003, RG-004, RG-005"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-03
  h(2, "UC-03 — Accéder aux données et résultats de recherche"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Visiteur, Chercheur, Partenaire"],
      ["Acteurs secondaires", "—"],
      ["Préconditions", "Des publications et datasets existent dans le système"],
      ["Postconditions", "L'utilisateur a accédé aux contenus autorisés"],
      ["Scénario nominal", "1. L'utilisateur accède au catalogue de publications ou datasets\n2. Il utilise les filtres (axe thématique, type, auteur, année, licence)\n3. Il sélectionne un contenu\n4. Le système vérifie ses droits d'accès\n5. Le contenu est affiché ou le fichier est téléchargeable"],
      ["Alt. / Exceptions", "Alt-1 : Dataset protégé + utilisateur non authentifié → redirection connexion\nAlt-2 : Partenaire accédant aux données de son domaine → accès accordé\nEx-1 : Fichier introuvable → erreur 404 signalée à l'admin"],
      ["Règles métier", "RG-006, RG-007, RG-008"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-04
  h(2, "UC-04 — Soumettre un contenu à validation"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Doctorant"],
      ["Acteurs secondaires", "Administrateur (notifié), Serveur mail"],
      ["Préconditions", "Le doctorant est authentifié"],
      ["Postconditions", "Le contenu est en statut 'en attente de validation', l'administrateur est notifié"],
      ["Scénario nominal", "1. Le doctorant accède à l'espace de soumission\n2. Il remplit le formulaire (titre, résumé, type, fichier, tags thématiques)\n3. Il soumet le contenu\n4. Le système crée une entrée en statut 'Soumis'\n5. Une notification est envoyée aux administrateurs de l'axe concerné"],
      ["Alt. / Exceptions", "Alt-1 : Sauvegarde en brouillon possible avant soumission finale\nEx-1 : Fichier trop volumineux (> limite définie) → message d'erreur\nEx-2 : Champs obligatoires manquants → erreur de validation inline"],
      ["Règles métier", "RG-009, RG-010, RG-011"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-05
  h(2, "UC-05 — Publier un article / contenu"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Chercheur"],
      ["Acteurs secondaires", "—"],
      ["Préconditions", "Le chercheur est authentifié avec le rôle 'Chercheur' ou supérieur"],
      ["Postconditions", "Le contenu est publié et visible sur le portail"],
      ["Scénario nominal", "1. Le chercheur accède à son espace de publication\n2. Il crée/édite le contenu (formulaire enrichi avec éditeur WYSIWYG)\n3. Il définit la visibilité (public / partenaires / interne)\n4. Il publie directement\n5. Le contenu est indexé et visible immédiatement"],
      ["Alt. / Exceptions", "Alt-1 : Planification de publication à une date future\nAlt-2 : Publication vers un portail thématique spécifique\nEx-1 : Doublon détecté → avertissement, publication bloquée"],
      ["Règles métier", "RG-012, RG-013"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-06
  h(2, "UC-06 — Valider un contenu soumis"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Administrateur (de l'axe concerné)"],
      ["Acteurs secondaires", "Doctorant (notifié), Serveur mail"],
      ["Préconditions", "Un contenu est en statut 'Soumis'"],
      ["Postconditions", "Le contenu est soit publié (statut 'Publié'), soit renvoyé avec commentaires (statut 'Révision requise')"],
      ["Scénario nominal", "1. L'administrateur reçoit une notification de nouvelle soumission\n2. Il consulte le contenu dans le back-office\n3. Il rédige un commentaire (optionnel)\n4. Il valide → statut passe à 'Publié', notification au doctorant\n   OU il rejette → statut passe à 'Révision requise', notification avec motif"],
      ["Alt. / Exceptions", "Alt-1 : Demande de modification mineure sans rejet complet\nEx-1 : Contenu supprimé par l'auteur entre soumission et validation"],
      ["Règles métier", "RG-011, RG-014, RG-015"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-07
  h(2, "UC-07 — Gérer les accès (ACL)"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Administrateur"],
      ["Acteurs secondaires", "Keycloak"],
      ["Préconditions", "L'administrateur est connecté avec les droits de gestion ACL"],
      ["Postconditions", "Les droits de l'utilisateur cible sont mis à jour dans Keycloak"],
      ["Scénario nominal", "1. L'administrateur accède au module de gestion des accès\n2. Il sélectionne un utilisateur\n3. Il consulte ses rôles et permissions actuels\n4. Il attribue / retire des rôles (par axe thématique si nécessaire)\n5. Les modifications sont propagées vers Keycloak\n6. L'utilisateur voit ses droits mis à jour à sa prochaine connexion"],
      ["Alt. / Exceptions", "Alt-1 : Gestion en lot (import CSV de permissions)\nEx-1 : Tentative d'élévation de privilèges non autorisée → rejet avec log d'audit"],
      ["Règles métier", "RG-016, RG-017, RG-018"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-08
  h(2, "UC-08 — Gérer les profils membres"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Administrateur"],
      ["Acteurs secondaires", "Utilisateur concerné (notifié)"],
      ["Préconditions", "L'administrateur est connecté"],
      ["Postconditions", "Le profil est créé / modifié / archivé"],
      ["Scénario nominal", "1. L'administrateur accède à l'annuaire des membres\n2. Il crée un nouveau profil (ou édite un existant) : nom, prénom, rôle, axe, photo, biographie\n3. Il enregistre\n4. L'utilisateur est notifié de la création/modification de son compte"],
      ["Alt. / Exceptions", "Alt-1 : Archivage d'un membre quittant le laboratoire (données conservées, compte désactivé)\nEx-1 : Email déjà existant → erreur de doublon"],
      ["Règles métier", "RG-019, RG-020"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-09
  h(2, "UC-09 — Gérer les datasets"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Chercheur, Administrateur"],
      ["Acteurs secondaires", "—"],
      ["Préconditions", "L'acteur est authentifié avec les droits adéquats"],
      ["Postconditions", "Le dataset est créé/modifié avec métadonnées complètes et droits d'accès définis"],
      ["Scénario nominal", "1. L'acteur accède au module dataset\n2. Il crée un nouveau dataset : titre, description, DOI (optionnel), fichiers, tags\n3. Il associe une licence (CC-BY, CC-BY-NC, données propriétaires...)\n4. Il définit les droits d'accès (public / partenaires / interne)\n5. Il publie le dataset dans le catalogue"],
      ["Alt. / Exceptions", "Alt-1 : Mise à jour d'une version (versionning du dataset)\nEx-1 : Format de fichier non supporté → liste des formats acceptés affichée"],
      ["Règles métier", "RG-007, RG-008, RG-021"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-10
  h(2, "UC-10 — Gérer son profil"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Chercheur, Doctorant, Partenaire"],
      ["Acteurs secondaires", "—"],
      ["Préconditions", "L'utilisateur est authentifié"],
      ["Postconditions", "Le profil est mis à jour"],
      ["Scénario nominal", "1. L'utilisateur accède à son espace personnel\n2. Il édite ses informations (photo, biographie, liens, thématiques de recherche)\n3. Il enregistre"],
      ["Alt. / Exceptions", "Ex-1 : Photo trop lourde → compression automatique ou message d'erreur"],
      ["Règles métier", "RG-019"],
    ],
    [2500, 6526]
  ),
  new Paragraph({ children: [], spacing: { before: 120 } }),

  // UC-12
  h(2, "UC-12 — Interagir avec le chatbot IA"),
  table(
    ["Attribut", "Détail"],
    [
      ["Acteur principal", "Tout utilisateur (authentifié ou non)"],
      ["Acteurs secondaires", "Système IA (Ollama)"],
      ["Préconditions", "Le service Ollama est opérationnel"],
      ["Postconditions", "L'utilisateur a reçu une réponse à sa question"],
      ["Scénario nominal", "1. L'utilisateur clique sur l'icône chatbot\n2. Le widget de conversation s'ouvre\n3. L'utilisateur saisit une question\n4. La requête est envoyée au backend\n5. Le backend interroge Ollama avec le contexte de la conversation\n6. La réponse est affichée dans le widget"],
      ["Alt. / Exceptions", "Ex-1 : Service Ollama indisponible → message de fallback ('Service temporairement indisponible')\nEx-2 : Question hors périmètre → réponse de redirection vers contact humain"],
      ["Règles métier", "RG-022"],
    ],
    [2500, 6526]
  ),

  pageBreak(),
  h(1, "9. Règles Métier"),
  table(
    ["ID", "Règle"],
    [
      ["RG-001", "Le portail public est accessible sans authentification pour les contenus marqués 'public'."],
      ["RG-002", "Les contenus marqués 'partenaires' ou 'interne' nécessitent une authentification et le rôle correspondant."],
      ["RG-003", "Après 5 tentatives de connexion échouées, le compte est temporairement verrouillé pendant 15 minutes."],
      ["RG-004", "Les tokens JWT ont une durée de vie de 30 minutes, renouvelables via refresh token pendant 8 heures."],
      ["RG-005", "Un administrateur peut forcer la déconnexion de tout utilisateur depuis le back-office."],
      ["RG-006", "Un dataset public peut être téléchargé sans authentification s'il est sous licence libre."],
      ["RG-007", "Tout dataset doit avoir une licence explicite avant d'être publié dans le catalogue."],
      ["RG-008", "L'accès à un dataset protégé requiert que l'utilisateur authentifié appartienne au groupe de partenaires autorisé pour ce dataset."],
      ["RG-009", "Un doctorant ne peut publier directement ; toute soumission passe obligatoirement par le workflow de validation."],
      ["RG-010", "Un contenu soumis sans les métadonnées obligatoires (titre, résumé, axe thématique, type) est rejeté par le système avant soumission."],
      ["RG-011", "L'administrateur responsable de l'axe thématique concerné est le validateur désigné pour les soumissions de cet axe."],
      ["RG-012", "Un chercheur peut publier directement des contenus associés à son axe thématique ou à ses propres travaux."],
      ["RG-013", "Un chercheur ne peut pas modifier les contenus publiés par un autre chercheur (sauf si administrateur)."],
      ["RG-014", "Tout rejet d'une soumission doit être accompagné d'un motif écrit notifié à l'auteur."],
      ["RG-015", "Un contenu validé est publié dans les 24h suivant la décision de validation."],
      ["RG-016", "Seul un administrateur de niveau supérieur peut attribuer le rôle 'administrateur d'axe' à un chercheur."],
      ["RG-017", "Un administrateur d'axe ne peut gérer les accès que des membres de son propre axe."],
      ["RG-018", "Toute modification des droits d'accès est tracée dans le journal d'audit avec l'identifiant de l'administrateur ayant effectué l'action."],
      ["RG-019", "Chaque membre du laboratoire possède un profil unique identifié par son adresse email institutionnelle."],
      ["RG-020", "Un compte désactivé ne peut plus se connecter mais ses publications restent accessibles."],
      ["RG-021", "Le versionning des datasets doit conserver toutes les versions précédentes accessibles via un historique."],
      ["RG-022", "Le chatbot IA ne doit pas stocker les conversations en dehors de la session en cours (données non persistées)."],
      ["RG-023", "La langue d'interface par défaut est le français ; la préférence de langue est mémorisée par cookie."],
      ["RG-024", "Les médias (images, vidéos) uploadés doivent être optimisés automatiquement (compression, format WebP)."],
      ["RG-025", "Toute convention de stage émise par le système doit être validée par un administrateur avant envoi."],
    ],
    [1400, 7626]
  ),

  pageBreak(),
  h(1, "10. Modèle du Domaine (Description Textuelle)"),
  p("Le domaine métier du portail UMMISCO s'articule autour des entités suivantes :"),

  h(2, "Entités et attributs essentiels"),
  table(
    ["Entité", "Attributs essentiels", "Relations principales"],
    [
      ["Utilisateur (superclasse)", "id, email, nom, prénom, mot_de_passe_hash, statut (actif/inactif), date_création, langue_préférence", "Est spécialisé en Chercheur, Doctorant, Partenaire, Administrateur"],
      ["Chercheur", "+ grade, orcid_id, page_personnelle_url, spécialité", "Appartient à 1..n AxeThematique ; auteur de 0..n Publications"],
      ["Doctorant", "+ directeur_thèse, année_inscription, titre_thèse", "Rattaché à 1 AxeThematique ; soumet 0..n Publications (via workflow)"],
      ["Partenaire", "+ organisation, domaine_accès", "Associé à 1..n AxeThematique ; accède à 0..n Datasets protégés"],
      ["Administrateur", "+ niveau (global / axe), axe_géré", "Gère 1..n AxeThematique ; valide des Publications"],
      ["Publication (superclasse)", "id, titre, résumé, date_publication, statut, visibilité, langue, auteurs[]", "Sous-types : Article, Document, Evenement, Dataset"],
      ["Article", "+ doi, revue_ou_conférence, lien_doi, mots_clés[]", "Sous-type de Publication"],
      ["Document", "+ type (rapport/thèse/présentation), fichier_url", "Sous-type de Publication"],
      ["Evenement", "+ date_début, date_fin, lieu, lien_inscription", "Sous-type de Publication"],
      ["Dataset", "+ licence, format, taille, version, métadonnées_json, fichiers_url[]", "Sous-type de Publication ; lié à 0..n ControleAcces"],
      ["AxeThematique", "id, nom, description, logo_url, responsable_id", "Contient 1..n Chercheurs ; regroupe 0..n Publications"],
      ["OutilDoctoral", "id, nom, url_iframe, description, doctorant_id, axe_id", "Appartient à 1 Doctorant ; intégré dans 1..n AxeThematique"],
      ["DiscussionChatbot", "id, session_id, messages[] (role, contenu, timestamp)", "Éphémère — non persisté entre sessions"],
      ["WorkflowValidation", "id, publication_id, soumetteur_id, validateur_id, statut, commentaire, date_soumission, date_décision", "Lié à 1 Publication ; implique 1 soumetteur et 1 validateur"],
      ["ControleAcces", "id, ressource_type, ressource_id, groupe_utilisateurs, permissions[]", "Contrôle l'accès à 1 ressource pour 1 groupe"],
    ],
    [1800, 3600, 3626]
  ),

  pageBreak(),
  // ══════════════════════════════════════════════
  // PHASE 4 — CONCEPTION ARCHITECTURALE
  // ══════════════════════════════════════════════
  new Paragraph({
    children: [new TextRun({ text: "PHASE 4 — CONCEPTION ARCHITECTURALE", size: 28, bold: true, color: COLOR.headerText })],
    shading: { fill: COLOR.header, type: ShadingType.CLEAR },
    spacing: { before: 240, after: 240 },
    indent: { left: 240, right: 240 }
  }),

  h(1, "11. Choix Architectural"),
  h(2, "Style retenu : Architecture Modulaire Monolithique (Modular Monolith) avec séparation Front-office / Back-office"),
  p("Compte tenu des contraintes identifiées — équipe de petite taille, capacités de maintenance interne limitées, hébergement sur infrastructure non-cloud-native — une architecture de microservices serait prématurée et difficile à maintenir. Le monolithe modulaire offre le meilleur compromis : simplicité de déploiement, cohérence transactionnelle native, maintenabilité accrue tout en préservant la possibilité d'une migration progressive vers des microservices si le besoin s'en fait sentir."),

  h(2, "Principes directeurs"),
  bullet("Séparation des responsabilités (SoC) : chaque module a un périmètre fonctionnel clairement délimité"),
  bullet("Couplage faible / cohésion forte : les modules communiquent via des interfaces bien définies"),
  bullet("Séparation physique et logique front-office / back-office : réduit la surface d'attaque"),
  bullet("Découplage de l'intégration externe : les outils tiers (iframes, chatbot) sont isolés dans des modules dédiés"),
  bullet("Infrastructure as Code : tout l'environnement de déploiement est versionné"),

  h(2, "Patterns architecturaux appliqués"),
  table(
    ["Pattern", "Application dans le projet"],
    [
      ["Repository Pattern", "Abstraction de l'accès aux données ; facilite les tests unitaires en mockant les repos"],
      ["MVC / MVT", "Structure de base du framework applicatif (Laravel ou Spring MVC)"],
      ["Service Layer", "Logique métier encapsulée dans des services indépendants des contrôleurs"],
      ["Observer / Event Bus", "Notifications asynchrones (publication validée → notifier l'auteur ; soumission → notifier admin)"],
      ["Strategy Pattern", "Gestion des différentes stratégies d'accès aux datasets selon le niveau de protection"],
      ["Facade", "Interface simplifiée pour l'intégration avec Keycloak et Ollama"],
      ["CQRS (léger)", "Séparation lectures/écritures pour le catalogue de datasets (optimisation des requêtes de recherche)"],
    ],
    [2500, 6526]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "12. Architecture en Couches"),
  table(
    ["Couche", "Rôle & Responsabilités", "Technologies envisagées", "Interactions"],
    [
      ["Présentation (UI)", "Rendu des interfaces utilisateurs (front-office public + back-office admin). Gestion de la navigation, validation côté client, i18n FR/EN.", "Vue.js 3 / Nuxt.js OU Blade (Laravel) / Thymeleaf (Spring)", "→ API Layer via HTTP/REST"],
      ["API / Contrôleurs", "Point d'entrée des requêtes HTTP. Routage, authentification des tokens JWT, sérialisation/désérialisation JSON, gestion des erreurs HTTP.", "Laravel Controllers / Spring MVC Controllers", "→ Service Layer ; ← Présentation"],
      ["Service (Logique métier)", "Orchestration des cas d'utilisation. Application des règles métier, workflows, notifications. Appels aux services externes (Keycloak API, Ollama).", "Services PHP/Java", "→ Repository Layer ; → Services externes"],
      ["Repository (Accès données)", "Abstraction de la persistance. CRUD, requêtes complexes, mapping objet-relationnel.", "Eloquent ORM (Laravel) / JPA-Hibernate (Spring)", "→ Base de données"],
      ["Infrastructure", "Connexions à la base de données, cache, stockage fichiers, envoi d'emails, file de messages.", "PostgreSQL, Redis, S3-compatible (MinIO), SMTP", "← toutes les couches supérieures"],
      ["Intégration externe", "Gestion des connexions Keycloak, Ollama, serveurs d'outils doctoraux.", "Keycloak Admin Client SDK, API REST Ollama", "← Service Layer"],
    ],
    [1800, 2800, 2200, 2226]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "13. Stack Technologique"),
  table(
    ["Composant", "Technologie retenue", "Alternatives écartées", "Justification"],
    [
      ["Backend (Option A)", "PHP 8.3 / Laravel 11", "Symfony, CodeIgniter", "Maîtrise interne, écosystème riche, Eloquent ORM, Sanctum/Passport pour JWT, communauté active au Sénégal"],
      ["Backend (Option B)", "Java 21 / Spring Boot 3", "Quarkus, Micronaut", "Plus robuste pour montée en charge, Spring Security natif, mais profils rares en interne"],
      ["Frontend", "Vue.js 3 + Nuxt.js (SSR)", "React/Next.js, Angular", "SSR pour SEO, i18n intégré, Composition API moderne, légèreté"],
      ["Base de données", "PostgreSQL 16", "MySQL, MongoDB", "Robustesse, JSON natif, full-text search, licences open-source, support des données géospatiales"],
      ["Cache", "Redis 7", "Memcached, APCu", "Persistance optionnelle, pub/sub pour notifications, sessions distribuées"],
      ["Authentification", "Keycloak 24", "Auth0, Firebase Auth", "Décision actée, souveraineté des données, SSO, RBAC intégré, déployable on-premise"],
      ["Stockage fichiers", "MinIO (S3-compatible)", "AWS S3, Cloudinary", "On-premise, souveraineté des données, API S3 compatible"],
      ["Chatbot IA", "Ollama (LLaMA 3 / Mistral)", "OpenAI API, Claude API", "Souveraineté des données, pas de coût d'API, déployable on-premise"],
      ["Conteneurisation", "Docker + Docker Compose", "Podman, Vagrant", "Standard industriel, reproductibilité des environnements"],
      ["Reverse proxy", "Nginx", "Apache, Caddy", "Performances, configuration fine, SSL termination, load balancing"],
      ["CI/CD", "GitHub Actions / GitLab CI", "Jenkins, CircleCI", "Intégration native avec les repos Git, gratuit pour projets publics"],
      ["Monitoring", "Prometheus + Grafana", "Datadog, New Relic", "Open-source, déployable on-premise, dashboards personnalisables"],
      ["Hébergement", "Infrastructure UCAD/UMMISCO", "Vercel, OVH, AWS", "Contrainte organisationnelle ; Vercel en fallback pour front-end statique"],
    ],
    [1800, 2200, 2200, 2826]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "14. Architecture de Déploiement"),
  h(2, "Infrastructure cible"),
  p("L'infrastructure repose sur des conteneurs Docker orchestrés manuellement (Docker Compose) dans un premier temps, avec une migration possible vers Kubernetes si la charge le justifie."),
  table(
    ["Environnement", "Description", "Services déployés"],
    [
      ["Dev (local)", "Poste développeur, Docker Compose", "App + DB + Redis + Keycloak + MinIO + Ollama (si RAM suffisante)"],
      ["Staging", "Serveur UMMISCO dédié aux tests", "Stack complète, données anonymisées, CI/CD déclenché sur branche develop"],
      ["Production", "Serveur(s) UMMISCO + Vercel (front fallback)", "Stack complète, certificats SSL Let's Encrypt, Nginx reverse proxy, backups automatisés"],
    ],
    [2000, 3000, 4026]
  ),

  new Paragraph({ children: [], spacing: { before: 160 } }),
  h(2, "Topologie réseau"),
  bullet("Zone DMZ : Nginx expose les ports 80/443 uniquement"),
  bullet("Réseau interne Docker : les services applicatifs communiquent entre eux sans exposition directe"),
  bullet("Keycloak accessible depuis l'interne et depuis le frontend (redirection OAuth)"),
  bullet("Ollama confiné au réseau interne ; accessible uniquement depuis le service backend"),
  bullet("MinIO accessible depuis le backend uniquement ; les fichiers publics servis via Nginx"),
  bullet("Base de données et Redis : accès strict depuis le backend uniquement, pas d'exposition externe"),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "15. Sécurité & Gestion des Accès"),
  h(2, "Stratégie d'authentification et d'autorisation"),
  table(
    ["Mécanisme", "Description"],
    [
      ["Authentification", "OAuth 2.0 / OpenID Connect via Keycloak. Flux Authorization Code avec PKCE pour le frontend SPA."],
      ["Tokens", "JWT (Access Token : 30 min, Refresh Token : 8h). Stockage côté client en mémoire (pas localStorage pour éviter XSS)."],
      ["Autorisation", "RBAC (Role-Based Access Control) via Keycloak Roles : VISITOR, RESEARCHER, DOCTORAL_STUDENT, PARTNER, AXE_ADMIN, SUPER_ADMIN."],
      ["ACL granulaire", "Permissions par ressource (dataset, publication, portail thématique) gérées via table ControleAcces en base."],
      ["Sessions back-office", "Session serveur avec jeton CSRF pour le back-office admin (couche de sécurité supplémentaire)."],
    ],
    [2500, 6526]
  ),

  new Paragraph({ children: [], spacing: { before: 160 } }),
  h(2, "Protection des données sensibles"),
  bullet("Chiffrement en transit : HTTPS/TLS 1.3 obligatoire sur tous les endpoints"),
  bullet("Chiffrement au repos : données sensibles chiffrées en base (données médicales si applicable)"),
  bullet("Hachage des mots de passe : bcrypt (coût ≥ 12) via Keycloak"),
  bullet("Variables d'environnement : secrets gérés via .env non commités + gestionnaire de secrets (Vault ou secrets Docker)"),
  bullet("Données de cardiologie : isolées, accès restreint, consultation juridique préalable obligatoire"),

  h(2, "Vecteurs d'attaque couverts"),
  table(
    ["Vecteur", "Mitigation"],
    [
      ["XSS (Cross-Site Scripting)", "Échappement automatique dans les templates, Content Security Policy (CSP), tokens JWT en mémoire"],
      ["CSRF", "Tokens CSRF sur les formulaires back-office, SameSite=Strict sur les cookies"],
      ["Injection SQL", "ORM paramétré (Eloquent/JPA), jamais de requêtes SQL concaténées"],
      ["IDOR (Insecure Direct Object Reference)", "Vérification systématique des droits d'accès côté serveur avant toute opération sur une ressource"],
      ["Brute Force", "Rate limiting Keycloak (5 tentatives → verrouillage 15 min), Nginx rate limiting"],
      ["Upload malveillant", "Validation MIME type, scan antivirus (ClamAV), stockage hors répertoire web"],
      ["Clickjacking", "Header X-Frame-Options DENY sauf pour les iframes d'outils doctoraux (whitelisting explicite)"],
    ],
    [2500, 6526]
  ),

  pageBreak(),
  // ══════════════════════════════════════════════
  // PHASE 5 — CONCEPTION DÉTAILLÉE
  // ══════════════════════════════════════════════
  new Paragraph({
    children: [new TextRun({ text: "PHASE 5 — CONCEPTION DÉTAILLÉE", size: 28, bold: true, color: COLOR.headerText })],
    shading: { fill: COLOR.header, type: ShadingType.CLEAR },
    spacing: { before: 240, after: 240 },
    indent: { left: 240, right: 240 }
  }),

  h(1, "16. Modèle de Données"),
  h(2, "Schéma relationnel (MLD — Merise)"),

  h(3, "Table : users"),
  table(
    ["Colonne", "Type", "Contraintes", "Description"],
    [
      ["id", "UUID", "PK, NOT NULL", "Identifiant unique"],
      ["email", "VARCHAR(255)", "UNIQUE, NOT NULL", "Email institutionnel"],
      ["nom", "VARCHAR(100)", "NOT NULL", "Nom de famille"],
      ["prenom", "VARCHAR(100)", "NOT NULL", "Prénom"],
      ["role", "ENUM('visitor','researcher','doctoral','partner','axe_admin','super_admin')", "NOT NULL", "Rôle principal"],
      ["axe_id", "UUID", "FK → axes.id, NULLABLE", "Axe thématique principal"],
      ["statut", "ENUM('actif','inactif','archivé')", "DEFAULT 'actif'", "État du compte"],
      ["photo_url", "VARCHAR(500)", "NULLABLE", "URL photo de profil"],
      ["biographie", "TEXT", "NULLABLE", "Bio publique"],
      ["langue_preference", "CHAR(2)", "DEFAULT 'fr'", "Langue interface"],
      ["keycloak_id", "VARCHAR(255)", "UNIQUE, NOT NULL", "ID Keycloak lié"],
      ["created_at", "TIMESTAMP", "NOT NULL", "Date de création"],
      ["updated_at", "TIMESTAMP", "NOT NULL", "Date de mise à jour"],
    ],
    [1800, 2500, 2500, 2226]
  ),

  new Paragraph({ children: [], spacing: { before: 120 } }),
  h(3, "Table : publications"),
  table(
    ["Colonne", "Type", "Contraintes", "Description"],
    [
      ["id", "UUID", "PK, NOT NULL", "Identifiant unique"],
      ["titre", "VARCHAR(500)", "NOT NULL", "Titre du contenu"],
      ["resume", "TEXT", "NOT NULL", "Résumé / abstract"],
      ["type", "ENUM('article','document','evenement','dataset','actualite')", "NOT NULL", "Type de publication"],
      ["statut", "ENUM('brouillon','soumis','en_revision','publie','archive')", "DEFAULT 'brouillon'", "État dans le workflow"],
      ["visibilite", "ENUM('public','partenaires','interne')", "DEFAULT 'public'", "Niveau d'accès"],
      ["langue", "CHAR(2)", "DEFAULT 'fr'", "Langue principale"],
      ["auteur_id", "UUID", "FK → users.id, NOT NULL", "Auteur principal"],
      ["axe_id", "UUID", "FK → axes.id, NULLABLE", "Axe thématique"],
      ["date_publication", "TIMESTAMP", "NULLABLE", "Date de mise en ligne"],
      ["created_at", "TIMESTAMP", "NOT NULL", "Date de création"],
      ["updated_at", "TIMESTAMP", "NOT NULL", "Dernière modification"],
    ],
    [1800, 2500, 2500, 2226]
  ),

  new Paragraph({ children: [], spacing: { before: 120 } }),
  h(3, "Table : datasets (hérite de publications)"),
  table(
    ["Colonne", "Type", "Contraintes", "Description"],
    [
      ["id", "UUID", "PK, FK → publications.id", "Identifiant (héritage)"],
      ["licence", "VARCHAR(100)", "NOT NULL", "Ex: CC-BY, CC-BY-NC, proprietary"],
      ["format", "VARCHAR(50)", "NOT NULL", "Ex: CSV, JSON, NetCDF"],
      ["taille_mo", "DECIMAL(10,2)", "NULLABLE", "Taille totale en Mo"],
      ["version", "VARCHAR(20)", "DEFAULT '1.0'", "Version du dataset"],
      ["doi", "VARCHAR(200)", "UNIQUE, NULLABLE", "DOI si disponible"],
      ["metadonnees", "JSONB", "NULLABLE", "Métadonnées structurées"],
    ],
    [1800, 2500, 2500, 2226]
  ),

  new Paragraph({ children: [], spacing: { before: 120 } }),
  h(3, "Autres tables clés"),
  table(
    ["Table", "Colonnes principales", "Relations"],
    [
      ["axes", "id, nom, description, logo_url, responsable_id (FK users)", "1..n users, 1..n publications"],
      ["workflow_validations", "id, publication_id (FK), soumetteur_id (FK), validateur_id (FK), statut, commentaire, date_soumission, date_decision", "1 publication, 1 soumetteur, 1 validateur"],
      ["controle_acces", "id, ressource_type, ressource_id, groupe, permissions (JSONB)", "N:N users ↔ publications/datasets"],
      ["outils_doctoraux", "id, nom, url_iframe, description, doctorant_id (FK), axe_id (FK), actif", "1 doctorant, 1 axe"],
      ["audit_logs", "id, utilisateur_id (FK), action, ressource_type, ressource_id, details (JSONB), ip, created_at", "Lecture seule, append-only"],
      ["newsletter_abonnes", "id, email, statut (actif/desabonne), created_at", "Indépendante"],
      ["publication_auteurs", "publication_id (FK), user_id (FK)", "Table de liaison N:N publications ↔ users"],
    ],
    [2000, 4000, 3026]
  ),

  pageBreak(),
  h(1, "17. Interfaces et API REST"),
  h(2, "Endpoints principaux"),
  table(
    ["Méthode", "Route", "Description", "Auth requise"],
    [
      ["GET", "/api/publications", "Liste paginée des publications (filtres : type, axe, statut, langue)", "Non (public)"],
      ["GET", "/api/publications/{id}", "Détail d'une publication", "Selon visibilité"],
      ["POST", "/api/publications", "Créer une publication / soumission", "Oui (researcher+)"],
      ["PUT", "/api/publications/{id}", "Modifier une publication", "Oui (auteur/admin)"],
      ["DELETE", "/api/publications/{id}", "Archiver une publication", "Oui (admin)"],
      ["POST", "/api/publications/{id}/submit", "Soumettre pour validation (doctorant)", "Oui (doctoral)"],
      ["POST", "/api/publications/{id}/validate", "Valider/rejeter une soumission", "Oui (axe_admin+)"],
      ["GET", "/api/datasets", "Catalogue datasets (filtres : licence, axe, format)", "Non (public)"],
      ["GET", "/api/datasets/{id}/download", "Télécharger un dataset", "Selon licence/droits"],
      ["POST", "/api/datasets", "Créer un dataset", "Oui (researcher+)"],
      ["GET", "/api/users", "Annuaire membres", "Oui (any authenticated)"],
      ["GET", "/api/users/{id}", "Profil d'un membre", "Non (public)"],
      ["PUT", "/api/users/{id}", "Mettre à jour un profil", "Oui (owner/admin)"],
      ["GET", "/api/axes", "Liste des axes thématiques", "Non"],
      ["GET", "/api/axes/{id}/publications", "Publications d'un axe", "Non (public)"],
      ["POST", "/api/chat", "Envoyer un message au chatbot IA", "Non"],
      ["POST", "/api/contact", "Formulaire de contact", "Non"],
      ["POST", "/api/newsletter/subscribe", "S'abonner à la newsletter", "Non"],
      ["GET", "/api/admin/users", "Gestion des utilisateurs (admin)", "Oui (super_admin)"],
      ["PUT", "/api/admin/acl/{userId}", "Modifier les droits d'un utilisateur", "Oui (admin+)"],
    ],
    [700, 2500, 3600, 2226]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "18. Flux et Processus Métier"),

  h(2, "Flux 1 : Publication directe par un chercheur"),
  numbered("Le chercheur s'authentifie via Keycloak → token JWT émis"),
  numbered("Le chercheur accède à son espace de publication dans le front-office"),
  numbered("Il remplit le formulaire de création (titre, résumé, type, axe, fichiers, visibilité)"),
  numbered("Il valide → le système vérifie que le rôle 'Researcher' est bien présent dans le token"),
  numbered("Aucun workflow de validation requis → statut directement 'Publié'"),
  numbered("Le contenu est indexé dans le moteur de recherche interne (PostgreSQL full-text)"),
  numbered("Le contenu apparaît sur le portail public (si visibilité = public) ou dans l'espace approprié"),

  new Paragraph({ children: [], spacing: { before: 120 } }),
  h(2, "Flux 2 : Soumission et validation d'un contenu de doctorant"),
  numbered("Le doctorant s'authentifie et accède à son espace de soumission"),
  numbered("Il crée le contenu et le soumet → statut passe à 'Soumis'"),
  numbered("Un événement est émis dans le bus d'événements interne"),
  numbered("Le système identifie le/les administrateurs de l'axe thématique concerné"),
  numbered("Une notification email est envoyée aux administrateurs"),
  numbered("L'administrateur consulte la soumission dans le back-office"),
  numbered("L'administrateur valide → statut passe à 'Publié' + notification au doctorant"),
  numbered("    OU l'administrateur rejette avec motif → statut 'Révision requise' + notification"),
  numbered("En cas de révision requise, le doctorant corrige et remet en soumission (retour à l'étape 2)"),

  new Paragraph({ children: [], spacing: { before: 120 } }),
  h(2, "Flux 3 : Accès à un dataset protégé par un partenaire"),
  numbered("Le partenaire accède au catalogue datasets"),
  numbered("Il sélectionne un dataset marqué 'Partenaires'"),
  numbered("Le système vérifie si l'utilisateur est authentifié → redirection Keycloak si non"),
  numbered("Keycloak retourne le token avec les groupes/rôles du partenaire"),
  numbered("Le backend vérifie la table controle_acces : le groupe du partenaire est-il autorisé sur ce dataset ?"),
  numbered("Si oui : accès accordé, téléchargement disponible, log d'audit enregistré"),
  numbered("Si non : erreur 403 avec message explicatif et lien vers le formulaire de contact"),

  new Paragraph({ children: [], spacing: { before: 120 } }),
  h(2, "Flux 4 : Interaction avec le chatbot IA"),
  numbered("L'utilisateur ouvre le widget chatbot (disponible sur toutes les pages)"),
  numbered("Il saisit une question"),
  numbered("Le frontend envoie la question + l'historique de session au backend via POST /api/chat"),
  numbered("Le backend construit un prompt avec le contexte UMMISCO (instructions système) + historique + question"),
  numbered("Le backend envoie la requête au service Ollama (API locale)"),
  numbered("Ollama génère une réponse via le modèle LLM configuré"),
  numbered("La réponse est retournée au frontend et affichée dans le widget"),
  numbered("L'historique est maintenu uniquement en mémoire côté client pour la durée de la session"),

  pageBreak(),
  // ══════════════════════════════════════════════
  // PHASE 6 — PLANIFICATION & RISQUES
  // ══════════════════════════════════════════════
  new Paragraph({
    children: [new TextRun({ text: "PHASE 6 — PLANIFICATION & RISQUES", size: 28, bold: true, color: COLOR.headerText })],
    shading: { fill: COLOR.header, type: ShadingType.CLEAR },
    spacing: { before: 240, after: 240 },
    indent: { left: 240, right: 240 }
  }),

  h(1, "19. Découpage en Modules / Composants"),
  table(
    ["Module", "Responsabilités", "Dépendances"],
    [
      ["AuthModule", "Authentification OAuth2/OIDC, gestion des sessions, refresh tokens, intégration Keycloak", "Keycloak, UserModule"],
      ["PublicPortalModule", "Rendu des pages publiques, navigation, i18n FR/EN, page d'accueil et pages institutionnelles", "ContentModule, DatasetModule"],
      ["ContentModule", "CRUD publications (articles, documents, événements, actualités), workflow de validation, éditeur WYSIWYG", "UserModule, AuthModule, NotificationModule"],
      ["DatasetModule", "Catalogue datasets, upload/download fichiers, gestion licences et accès, versionning", "AuthModule, StorageService, ControleAccesModule"],
      ["UserModule", "Annuaire membres, profils, vitrines personnelles, gestion par admin", "AuthModule, Keycloak"],
      ["AdminModule (Back-office)", "Interface d'administration sécurisée, gestion ACL, tableaux de bord, audit logs", "Tous les modules, AuthModule"],
      ["AxeThematiqueModule", "Portails thématiques, gestion des axes, membres et publications par axe", "ContentModule, UserModule"],
      ["IntegrationModule", "Intégration iframes outils doctoraux, configuration et affichage", "AuthModule"],
      ["AIModule", "Chatbot IA, proxy vers Ollama, gestion du contexte de session", "Service Ollama (externe)"],
      ["NotificationModule", "Envoi d'emails (soumission, validation, newsletter), templates email", "Serveur SMTP"],
      ["SearchModule", "Recherche full-text sur publications, datasets, membres et axes", "PostgreSQL (full-text), ContentModule, DatasetModule"],
      ["ControleAccesModule", "Gestion fine des permissions par ressource, synchronisation Keycloak groups", "Keycloak, AuthModule"],
      ["AuditModule", "Journalisation des actions sensibles, conservation des logs, interface de consultation", "Tous les modules"],
    ],
    [2200, 4000, 2826]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "20. Plan de Développement Indicatif"),
  table(
    ["Itération", "Objectif", "Fonctionnalités livrées", "Durée estimée"],
    [
      ["Sprint 0", "Fondations & Infrastructure", "Mise en place environnement Docker, CI/CD, Keycloak configuré, DB initialisée, structure du projet", "2 semaines"],
      ["Sprint 1", "Authentification & Utilisateurs", "AuthModule complet (login/logout/refresh), UserModule (CRUD profils), rôles Keycloak définis", "2 semaines"],
      ["Sprint 2", "Portail public (fondation)", "PublicPortalModule : pages institutionnelles, navigation, i18n FR/EN, page d'accueil", "2 semaines"],
      ["Sprint 3", "Gestion des contenus (base)", "ContentModule : CRUD publications pour chercheurs, publication directe, affichage portail public", "3 semaines"],
      ["Sprint 4", "Workflow de validation", "Soumission doctorants, circuit de validation, notifications email, statuts, back-office validation", "2 semaines"],
      ["Sprint 5", "Datasets & Accès", "DatasetModule : catalogue, upload, licences, téléchargement, ControleAccesModule", "3 semaines"],
      ["Sprint 6", "Portails thématiques & Admin", "AxeThematiqueModule, AdminModule (back-office complet, ACL, audit logs)", "3 semaines"],
      ["Sprint 7", "Intégrations & IA", "IntegrationModule (iframes doctoraux), AIModule (chatbot Ollama), formulaires de contact", "2 semaines"],
      ["Sprint 8", "Recherche, Newsletter & Finitions", "SearchModule, newsletter, optimisations performance, accessibilité WCAG, SEO", "2 semaines"],
      ["Sprint 9", "Tests & Mise en production", "Tests E2E, audit sécurité, tests de charge, documentation, déploiement production", "3 semaines"],
    ],
    [1400, 1800, 4200, 1626]
  ),
  note("Durée totale indicative : ~24 semaines (~6 mois). À affiner selon la taille de l'équipe et les arbitrages de stack."),

  pageBreak(),
  h(1, "21. Analyse des Risques"),
  table(
    ["#", "Risque", "Catégorie", "Prob.", "Impact", "Score", "Mitigation"],
    [
      ["R-01", "Stack technologique non arrêté avant démarrage", "Technique", "H (3)", "H (3)", "9", "Arbitrage obligatoire en Sprint 0 ; prototype sur les deux stacks si nécessaire"],
      ["R-02", "Iframes outils doctoraux bloquées (CORS, HTTPS)", "Technique", "H (3)", "M (2)", "6", "Prototype iframe en environnement cible avant Sprint 7 ; validation technique dès Sprint 0"],
      ["R-03", "Choix IA (OpenAI vs Ollama) impacte hébergement", "Technique", "M (2)", "H (3)", "6", "Décision Ollama actée pour souveraineté ; si GPU insuffisant → repli sur modèle plus léger (Phi-3)"],
      ["R-04", "Données de cardiologie : non-conformité juridique", "Légal", "H (3)", "H (3)", "9", "Consultation juridique avant tout développement lié ; isolation complète du module"],
      ["R-05", "Capacités maintenance interne limitées (profils rares)", "Organisationnel", "M (2)", "H (3)", "6", "Privilégier PHP/Laravel si maîtrise interne ; documentation architecture dès Sprint 0"],
      ["R-06", "Indisponibilité infrastructure UCAD (SLA non garantis)", "Externe", "M (2)", "H (3)", "6", "Définir SLA avec DSI UCAD ; Vercel comme fallback front-end ; backups réguliers off-site"],
      ["R-07", "Dérive du périmètre (scope creep)", "Organisationnel", "H (3)", "M (2)", "6", "Backlog priorisé et figé par sprint ; tout ajout passe par validation Product Owner"],
      ["R-08", "Sécurité : fuite de données sensibles", "Technique", "M (2)", "H (3)", "6", "Audit sécurité OWASP en Sprint 9 ; tests de pénétration ; chiffrement des données sensibles"],
      ["R-09", "Keycloak : complexité de configuration", "Technique", "M (2)", "M (2)", "4", "Formation équipe sur Keycloak ; utiliser Keycloak Admin CLI pour scripts reproductibles"],
      ["R-10", "Dépendance à un développeur unique sur un module critique", "Organisationnel", "M (2)", "M (2)", "4", "Documentation inline obligatoire ; revues de code croisées ; bus factor > 1 sur AuthModule"],
    ],
    [500, 2300, 1200, 700, 700, 700, 2926]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "22. Plan de Tests"),
  h(2, "Types de tests"),
  table(
    ["Type de test", "Périmètre", "Outils", "Coverage / Critère"],
    [
      ["Tests unitaires", "Services, Repositories, logique métier (règles RG-*)", "PHPUnit (Laravel) / JUnit 5 (Spring) + Mockito", "≥ 70% de coverage sur les classes Service"],
      ["Tests d'intégration", "API REST, intégration Keycloak, accès base de données", "Pest (Laravel) / Spring Boot Test + Testcontainers", "Tous les endpoints couverts"],
      ["Tests End-to-End (E2E)", "Flux métier complets (publication, validation, téléchargement, auth)", "Cypress ou Playwright", "UC-01 à UC-09 couverts"],
      ["Tests de performance", "Temps de réponse sous charge, LCP Core Web Vitals", "k6 ou Apache JMeter, Lighthouse", "500 utilisateurs simultanés, LCP < 2,5s"],
      ["Tests de sécurité", "OWASP Top 10, injection, XSS, CSRF, IDOR", "OWASP ZAP, Snyk, analyse statique (SonarQube)", "0 vulnérabilité critique"],
      ["Tests d'accessibilité", "Conformité WCAG 2.1 AA sur les pages publiques", "axe-core, WAVE", "0 erreur WCAG 2.1 AA"],
      ["UAT (Recette utilisateur)", "Validation fonctionnelle avec les utilisateurs réels (chercheurs, doctorants)", "Scénarios manuels, retours collectés", "Validation de tous les Must de la MoSCoW"],
    ],
    [1800, 2600, 2200, 2426]
  ),

  h(2, "Priorités de tests"),
  bullet("AuthModule : critique → 100% unitaire + intégration Keycloak"),
  bullet("WorkflowValidation : critique métier → unitaire + E2E complet"),
  bullet("ControleAccesModule : sécurité → unitaire + tests de sécurité IDOR"),
  bullet("DatasetModule : accès protégé → intégration + sécurité"),
  bullet("API publique : performance + accessibilité"),

  h(2, "Stratégie"),
  bullet("Approche TDD sur les modules AuthModule et ContentModule (critiques)"),
  bullet("Tests d'intégration dans CI/CD (GitHub Actions) : déclenchés sur chaque pull request"),
  bullet("Tests E2E déclenchés sur la branche develop avant merge en main"),
  bullet("Audit sécurité (ZAP scan) exécuté en Sprint 9 sur l'environnement staging"),

  pageBreak(),
  // ══════════════════════════════════════════════
  // SYNTHÈSE FINALE
  // ══════════════════════════════════════════════
  new Paragraph({
    children: [new TextRun({ text: "SYNTHÈSE FINALE", size: 28, bold: true, color: COLOR.headerText })],
    shading: { fill: COLOR.header, type: ShadingType.CLEAR },
    spacing: { before: 240, after: 240 },
    indent: { left: 240, right: 240 }
  }),

  h(1, "23. Récapitulatif des Décisions Clés"),
  table(
    ["Décision", "Option retenue", "Options écartées", "Justification"],
    [
      ["Architecture applicative", "Monolithe modulaire", "Microservices, SOA", "Équipe réduite, maintenance plus simple, pas de surcharge opérationnelle"],
      ["Authentification", "Keycloak (OAuth2/OIDC)", "Auth0, Firebase Auth, custom JWT", "Décision actée, souveraineté des données, SSO, RBAC, on-premise"],
      ["Base de données", "PostgreSQL", "MySQL, MongoDB, MariaDB", "Robustesse, JSONB, full-text search, réplication native"],
      ["Chatbot IA", "Ollama (LLM local)", "OpenAI API, Google Gemini", "Souveraineté des données, coût opérationnel nul, pas de dépendance à un tiers"],
      ["Intégration outils doctoraux", "Iframes HTML", "Redéveloppement natif, micro-frontends", "Découplage total, maintenance côté doctorant, zéro surcharge pour le portail"],
      ["Séparation front/back-office", "Séparation physique et logique", "Même application avec rôles", "Réduction de la surface d'attaque, sécurité renforcée"],
      ["Stockage fichiers", "MinIO (S3-compatible)", "AWS S3, Cloudinary, système de fichiers local", "On-premise, souveraineté, API S3 compatible, pas de coût cloud"],
      ["Stack à arbitrer", "PHP/Laravel (recommandé) OU Java/Spring Boot", "Node.js, Python/Django", "Maîtrise interne chez Laravel ; Spring Boot plus robuste mais maintenance rare"],
      ["Hébergement", "Infrastructure UCAD/UMMISCO + Vercel fallback", "AWS, OVH, DigitalOcean", "Contrainte organisationnelle et budgétaire"],
    ],
    [2000, 1800, 2200, 3026]
  ),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "24. Questions Ouvertes & Points à Clarifier"),
  numbered("Stack technologique : PHP/Laravel vs Java/Spring Boot — arbitrage à finaliser AVANT le Sprint 0 (critère : quelles compétences sont disponibles dans l'équipe de développement ?)"),
  numbered("Infrastructure UCAD/UMMISCO : quelles sont les garanties de disponibilité ? Existe-t-il un SLA formalisé avec la DSI ? Quelle est la capacité mémoire RAM disponible pour Ollama ?"),
  numbered("Données de cardiologie (capteurs arbres) : une consultation juridique a-t-elle été initiée ? Quelles sont les contraintes légales sénégalaises applicables aux données de santé ?"),
  numbered("Rôle des médias : doivent-ils disposer d'un espace différencié en v1 ou rester assimilés aux visiteurs ? Quelles données spécifiques souhaitent-ils accéder ?"),
  numbered("Bailleurs de fonds : quels indicateurs de dynamisme du laboratoire souhaitent-ils consulter ? Sous quelle forme (tableaux de bord, rapports PDF, API) ?"),
  numbered("Gestion des conventions de stage et bons d'achat : ces fonctionnalités sont classées 'Could/Won't (v1)' — confirmer leur exclusion du périmètre initial avec le client."),
  numbered("Outil Evelop et outil carbone d'Osman : disposent-ils d'URLs stables et accessibles en HTTPS ? Y a-t-il des restrictions CORS à anticiper ?"),
  numbered("Modèle de déploiement Ollama : le serveur UMMISCO dispose-t-il d'un GPU ? Quel modèle LLM est envisagé (LLaMA 3, Mistral, Phi-3) en fonction des ressources disponibles ?"),
  numbered("Newsletter : un outil d'envoi email existe-t-il déjà (Mailchimp, serveur SMTP institutionnel) ou faut-il en provisionner un ?"),
  numbered("Multilingue : les contenus scientifiques (publications, descriptions de datasets) seront-ils traduits manuellement ou via traduction automatique intégrée ?"),
  numbered("Définition du DOI : le laboratoire dispose-t-il d'un préfixe DOI pour ses publications ? Sinon, est-ce dans le périmètre du projet d'en obtenir un ?"),
  numbered("Niveaux d'accès partenaires : comment les groupes de partenaires et leurs accès aux datasets seront-ils initialisés ? Qui gère l'onboarding d'un nouveau partenaire ?"),

  new Paragraph({ children: [], spacing: { before: 240 } }),
  h(1, "25. Prérequis Avant de Commencer le Code"),
  h(2, "Décisions à valider"),
  bullet("[ ] Stack technologique arbitré (PHP/Laravel vs Java/Spring Boot)"),
  bullet("[ ] Décision IA confirmée (Ollama + modèle sélectionné selon ressources GPU)"),
  bullet("[ ] Périmètre v1 validé par le Product Owner (conventions de stage / bons d'achat OUT)"),
  bullet("[ ] Consultation juridique initiée pour les données de cardiologie"),
  bullet("[ ] SLA infrastructure UCAD/UMMISCO défini ou Vercel configuré comme fallback"),

  h(2, "Environnement technique"),
  bullet("[ ] Serveur(s) de développement / staging provisionnés (ou VM locales Docker)"),
  bullet("[ ] Keycloak installé et configuré (realm UMMISCO, clients, rôles de base)"),
  bullet("[ ] PostgreSQL 16 installé, base de données de dev créée"),
  bullet("[ ] Redis installé et accessible"),
  bullet("[ ] MinIO installé et buckets initialisés (publications, datasets, avatars)"),
  bullet("[ ] Ollama installé avec le modèle LLM choisi téléchargé"),
  bullet("[ ] Domaine(s) DNS configurés (dev.ummisco.xxx, staging.ummisco.xxx, ummisco.xxx)"),
  bullet("[ ] Certificats SSL/TLS provisionnés (Let's Encrypt)"),
  bullet("[ ] Repository Git créé (GitHub / GitLab), branches main/develop/feature/* définies"),
  bullet("[ ] Pipeline CI/CD configuré (GitHub Actions ou GitLab CI) : lint + tests + build"),

  h(2, "Outils & accès"),
  bullet("[ ] Gestionnaire de tickets / backlog configuré (Jira, Linear, Trello, ou GitHub Projects)"),
  bullet("[ ] Espace de documentation partagé créé (Confluence, Notion, ou dossier docs/ dans le repo)"),
  bullet("[ ] Accès aux outils doctoraux confirmés (URLs des iframes d'Evelop et outil carbone)"),
  bullet("[ ] Serveur SMTP disponible et credentials en main"),
  bullet("[ ] SonarQube ou autre outil d'analyse statique configuré"),

  h(2, "Équipe"),
  bullet("[ ] Rôles définis dans l'équipe : Tech Lead, Backend Dev(s), Frontend Dev(s), DevOps, UX (si applicable)"),
  bullet("[ ] Formation sur Keycloak planifiée pour les développeurs"),
  bullet("[ ] Conventions de code et de commit définies (ESLint, PHP CS Fixer / Checkstyle, Conventional Commits)"),
  bullet("[ ] Product Owner identifié et disponible pour les rituels agiles"),
  bullet("[ ] Processus de revue de code défini (PR obligatoire, 1 reviewer minimum)"),

];

// ─── BUILD DOCUMENT ──────────────────────────────────────────────────────────

const doc = new Document({
  numbering: {
    config: [
      {
        reference: "bullets",
        levels: [{
          level: 0, format: LevelFormat.BULLET, text: "•", alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 720, hanging: 360 } } }
        }, {
          level: 1, format: LevelFormat.BULLET, text: "◦", alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 1080, hanging: 360 } } }
        }]
      },
      {
        reference: "numbers",
        levels: [{
          level: 0, format: LevelFormat.DECIMAL, text: "%1.", alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 720, hanging: 360 } } }
        }]
      }
    ]
  },
  styles: {
    default: {
      document: { run: { font: "Arial", size: 22 } }
    },
    paragraphStyles: [
      {
        id: "Heading1", name: "Heading 1", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 32, bold: true, font: "Arial", color: COLOR.primary },
        paragraph: { spacing: { before: 360, after: 120 }, outlineLevel: 0 }
      },
      {
        id: "Heading2", name: "Heading 2", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 26, bold: true, font: "Arial", color: COLOR.secondary },
        paragraph: { spacing: { before: 240, after: 80 }, outlineLevel: 1 }
      },
      {
        id: "Heading3", name: "Heading 3", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 22, bold: true, font: "Arial", color: COLOR.accent },
        paragraph: { spacing: { before: 180, after: 60 }, outlineLevel: 2 }
      },
    ]
  },
  sections: [{
    properties: {
      page: {
        size: { width: 11906, height: 16838 },
        margin: { top: 1440, right: 1080, bottom: 1440, left: 1080 }
      }
    },
    headers: {
      default: new Header({
        children: [new Paragraph({
          children: [
            new TextRun({ text: "UMMISCO — Dossier de Conception Technique — Portail Web", size: 18, color: "888888" })
          ],
          border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: COLOR.secondary, space: 1 } },
          spacing: { after: 120 }
        })]
      })
    },
    footers: {
      default: new Footer({
        children: [new Paragraph({
          children: [
            new TextRun({ text: "Version 1.0  |  Confidentiel  |  Page ", size: 18, color: "888888" }),
            new TextRun({ children: [PageNumber.CURRENT], size: 18, color: "888888" }),
            new TextRun({ text: " / ", size: 18, color: "888888" }),
            new TextRun({ children: [PageNumber.TOTAL_PAGES], size: 18, color: "888888" }),
          ],
          alignment: AlignmentType.RIGHT,
          border: { top: { style: BorderStyle.SINGLE, size: 4, color: COLOR.secondary, space: 1 } },
          spacing: { before: 120 }
        })]
      })
    },
    children
  }]
});

Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync("C:\\Users\\USER\\Documents\\DIC1\\Semestre2\\IPDL\\portail_web\\UMMISCO_Dossier_Conception.docx", buffer);
  console.log("Document generated successfully.");
});