<?php

namespace App\Modules\PublicPortal\Controllers;

use App\Modules\Content\Models\Publication;
use App\Modules\User\Models\AxeThematique;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * PublicPortalController — Portail public institutionnel UMMISCO
 *
 * Fournit les pages accessibles sans authentification :
 *   - Page d'accueil (axes, actualités, statistiques)
 *   - Catalogue des publications publiques
 *   - Liste des axes thématiques
 *   - Inscription newsletter et formulaire de contact
 */
class PublicPortalController extends Controller
{
    // ── Page d'accueil ──────────────────────────────────────────────────────

    /**
     * Affiche la page d'accueil du portail.
     * Données : axes actifs, actualités récentes, statistiques du laboratoire.
     */
    public function home()
    {
        // Axes thématiques actifs (ordonnés par ordre_affichage)
        $axes = AxeThematique::actif()->get();

        $userId   = session('user_id');
        $userRole = session('user_role');

        // Publications récentes visibles pour l'utilisateur
        $recentPublications = Publication::visibleForUser($userId, $userRole)
            ->with(['auteur:id,nom,prenom,photo_url', 'axe:id,nom_fr,nom_en,code,couleur_hex'])
            ->orderBy('date_publication', 'desc')
            ->limit(6)
            ->get();

        // Statistiques globales du laboratoire (vue SQL)
        $stats = DB::table('v_statistiques_laboratoire')->first();

        return view('public.home', compact('axes', 'recentPublications', 'stats'));
    }

    // ── Catalogue des publications ──────────────────────────────────────────

    /**
     * Affiche le catalogue de publications publiques avec filtres.
     * Supporte la recherche full-text PostgreSQL (fts_fr / fts_en).
     */
    public function publications(Request $request)
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        $query = Publication::visibleForUser($userId, $userRole)
            ->with(['auteur:id,nom,prenom,photo_url', 'axe:id,nom_fr,nom_en,code,couleur_hex']);

        // Filtre par recherche full-text
        if ($search = $request->input('q')) {
            $lang = current_locale();
            $query->search($search, $lang);
        }

        // Filtre par type de publication
        if ($type = $request->input('type')) {
            $query->byType($type);
        }

        // Filtre par axe thématique
        if ($axeId = $request->input('axe')) {
            $query->byAxe($axeId);
        }

        // Tri par défaut : date de publication décroissante
        if (!$search) {
            $query->orderBy('date_publication', 'desc');
        }

        // Pagination
        $perPage = (int) DB::table('parametres_systeme')
            ->where('cle', 'nb_publications_page')
            ->value('valeur') ?: 12;

        $publications = $query->paginate($perPage)->withQueryString();

        // Axes pour les filtres
        $axes = AxeThematique::actif()->get();

        // Types disponibles pour le filtre
        $types = [
            Publication::TYPE_ARTICLE      => 'Articles',
            Publication::TYPE_NEWS         => 'Actualités',
            Publication::TYPE_EVENT        => 'Événements',
            Publication::TYPE_THESIS       => 'Thèses',
            Publication::TYPE_REPORT       => 'Rapports',
            Publication::TYPE_DATASET      => 'Datasets',
            Publication::TYPE_PRESENTATION => 'Présentations',
            Publication::TYPE_DOCUMENT     => 'Recherches en cours',
        ];

        return view('public.publications', compact('publications', 'axes', 'types'));
    }

    // ── Axes thématiques ────────────────────────────────────────────────────

    /**
     * Affiche la liste des axes de recherche.
     */
    public function axes()
    {
        $axes = AxeThematique::actif()
            ->withCount(['publications' => function ($q) {
                $q->where('statut', 'published')
                  ->where('visibilite', 'public')
                  ->whereNull('deleted_at');
            }])
            ->withCount(['membres'])
            ->with('responsable:id,nom,prenom,photo_url,titre_academique')
            ->get();

        return view('public.axes', compact('axes'));
    }

    // ── Détail d'une publication ──────────────────────────────────────

    /**
     * Affiche le détail d'une publication publique.
     */
    public function show(string $id)
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        $publication = Publication::visibleForUser($userId, $userRole)
            ->with([
                'auteur:id,nom,prenom,photo_url,titre_academique,orcid_id,email',
                'axe:id,nom_fr,nom_en,code,couleur_hex',
                'document',
                'dataset.fichiers',
            ])
            ->findOrFail($id);

        // Publications similaires du même axe visibles pour l'utilisateur
        $similar = [];
        if ($publication->axe_id) {
            $similar = Publication::visibleForUser($userId, $userRole)
                ->where('axe_id', $publication->axe_id)
                ->where('id', '!=', $id)
                ->with('auteur:id,nom,prenom')
                ->orderBy('date_publication', 'desc')
                ->limit(3)
                ->get();
        }

        $canDownload = $this->checkDownloadPermission($publication, $userId, $userRole);

        return view('public.publication', compact('publication', 'similar', 'canDownload'));
    }

    // ── Newsletter ──────────────────────────────────────────────────────────

    /**
     * Inscription à la newsletter.
     */
    public function subscribeNewsletter(Request $request)
    {
        $validated = $request->validate([
            'email'  => 'required|email|max:255',
            'nom'    => 'nullable|string|max:100',
            'langue' => 'nullable|in:fr,en',
        ]);

        DB::table('newsletter_abonnes')->updateOrInsert(
            ['email' => $validated['email']],
            [
                'nom'           => $validated['nom'] ?? null,
                'langue'        => $validated['langue'] ?? 'fr',
                'actif'         => true,
                'token_unsub'   => bin2hex(random_bytes(32)),
                'subscribed_at' => now(),
                'created_at'    => now(),
            ]
        );

        return back()->with('success', 'Inscription à la newsletter confirmée !');
    }

    // ── Formulaire de contact ───────────────────────────────────────────────

    /**
     * Traite le formulaire de contact.
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'nom'             => 'required|string|max:200',
            'email'           => 'required|email|max:255',
            'organisation'    => 'nullable|string|max:200',
            'sujet'           => 'required|string|max:300',
            'message'         => 'required|string|max:5000',
            'type_demande'    => 'nullable|in:contact,collaboration,prestation',
            'axe_concerne_id' => 'nullable|uuid|exists:axes_thematiques,id',
        ]);

        DB::table('demandes_contact')->insert([
            'id'              => \Illuminate\Support\Str::uuid(),
            'nom'             => $validated['nom'],
            'email'           => $validated['email'],
            'organisation'    => $validated['organisation'] ?? null,
            'sujet'           => $validated['sujet'],
            'message'         => $validated['message'],
            'type_demande'    => $validated['type_demande'] ?? 'contact',
            'axe_concerne_id' => $validated['axe_concerne_id'] ?? null,
            'ip_address'      => $request->ip(),
            'created_at'      => now(),
        ]);

        return back()->with('success', 'Votre message a été envoyé avec succès. Nous vous répondrons rapidement.');
    }

    /**
     * Télécharge de manière sécurisée un document rattaché à une publication.
     */
    public function downloadDocument(string $id)
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        // Vérifie l'accès à la publication via le scope
        $publication = Publication::visibleForUser($userId, $userRole)->findOrFail($id);

        // Vérifie les droits de téléchargement spécifiques
        if (!$this->checkDownloadPermission($publication, $userId, $userRole)) {
            abort(403, "Vous n'êtes pas autorisé à télécharger ce document.");
        }

        // Récupère les métadonnées du document rattaché
        $document = DB::table('documents')->where('publication_id', $publication->id)->first();
        if (!$document) {
            abort(404, 'Fichier non trouvé ou non rattaché.');
        }

        $fichierUrl = $document->fichier_url;
        if (empty($fichierUrl) || $fichierUrl === '0') {
            $fichierUrl = 'publications/' . $publication->id . '/' . ($document->fichier_nom ?: 'document.pdf');
            DB::table('documents')
                ->where('publication_id', $publication->id)
                ->update(['fichier_url' => $fichierUrl]);
        }

        // Si le fichier physique n'existe pas dans MinIO, on génère un mock pour les besoins du développement
        try {
            if (!\Illuminate\Support\Facades\Storage::disk('minio')->exists($fichierUrl)) {
                \Illuminate\Support\Facades\Storage::disk('minio')->put(
                    $fichierUrl,
                    "Contenu du document factice : " . $document->fichier_nom . "\nPublication ID : " . $publication->id . "\nTitre : " . $publication->titre_fr
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to check or put document in MinIO: " . $e->getMessage());
        }

        return \Illuminate\Support\Facades\Storage::disk('minio')->download($fichierUrl, $document->fichier_nom);
    }

    /**
     * Télécharge de manière sécurisée un fichier rattaché à un dataset.
     */
    public function downloadDatasetFile(string $id)
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        // Récupère les métadonnées du fichier du dataset
        $fichier = DB::table('datasets_fichiers')->where('id', $id)->first();
        if (!$fichier) {
            abort(404, 'Fichier de dataset non trouvé.');
        }

        // Vérifie l'accès au dataset parent (qui est une publication)
        $publication = Publication::visibleForUser($userId, $userRole)->findOrFail($fichier->dataset_id);

        // Vérifie les droits de téléchargement spécifiques
        if (!$this->checkDownloadPermission($publication, $userId, $userRole)) {
            abort(403, "Vous n'êtes pas autorisé à télécharger ce document.");
        }

        $cheminMinio = $fichier->chemin_minio;
        if (empty($cheminMinio) || $cheminMinio === '0') {
            $cheminMinio = 'datasets/' . $fichier->dataset_id . '/' . ($fichier->nom ?: 'dataset.bin');
            DB::table('datasets_fichiers')
                ->where('id', $fichier->id)
                ->update(['chemin_minio' => $cheminMinio]);
        }

        // Si le fichier n'existe pas dans MinIO, on génère un mock
        try {
            if (!\Illuminate\Support\Facades\Storage::disk('minio')->exists($cheminMinio)) {
                \Illuminate\Support\Facades\Storage::disk('minio')->put(
                    $cheminMinio,
                    "Contenu factice du dataset : " . $fichier->nom . "\nDataset ID : " . $fichier->dataset_id . "\nTitre : " . $publication->titre_fr
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to check or put dataset file in MinIO: " . $e->getMessage());
        }

        return \Illuminate\Support\Facades\Storage::disk('minio')->download($cheminMinio, $fichier->nom);
    }

    /**
     * Vérifie si l'utilisateur est autorisé à télécharger un document.
     */
    private function checkDownloadPermission(Publication $publication, ?string $userId, ?string $userRole): bool
    {
        if ($userRole === 'super_admin') {
            return true;
        }

        if ($userId && $publication->auteur_id === $userId) {
            return true;
        }

        if ($userId && $userRole === 'axe_admin' && $publication->axe_id) {
            $isAxeAdmin = DB::table('axes_thematiques')
                ->where('id', $publication->axe_id)
                ->where('responsable_id', $userId)
                ->exists();
            if ($isAxeAdmin) {
                return true;
            }
        }

        if ($publication->visibilite === 'public') {
            return true;
        }

        // Si la publication est interne et que l'utilisateur est connecté (membre du labo)
        if ($userId && $publication->visibilite === 'internal') {
            return true;
        }

        // Si la publication est pour les partenaires et que l'utilisateur connecté n'est pas un simple visiteur/public
        if ($userId && $publication->visibilite === 'partners' && $userRole !== 'visitor') {
            return true;
        }

        // Vérification des droits explicites via la table controle_acces (ACL)
        if ($userRole) {
            $hasDownloadPermission = DB::table('controle_acces')
                ->where('ressource_type', 'publication')
                ->where('ressource_id', $publication->id)
                ->where('groupe', $userRole)
                ->whereRaw("? = ANY(permissions)", ['download'])
                ->exists();
            if ($hasDownloadPermission) {
                return true;
            }
        }

        return false;
    }

    // ── Nouveaux Modules UMMISCO ─────────────────────────────────────────────

    /**
     * Présentation de l'unité.
     */
    public function presentation()
    {
        return view('public.presentation');
    }

    /**
     * Priorités scientifiques.
     */
    public function priorites()
    {
        return view('public.priorites');
    }

    /**
     * Annuaire des membres.
     */
    public function membres()
    {
        $chercheurs = \App\Modules\User\Models\User::where('role', 'researcher')
            ->where('statut', 'active')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $doctorants = \App\Modules\User\Models\User::where('role', 'doctoral_student')
            ->where('statut', 'active')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return view('public.membres', compact('chercheurs', 'doctorants'));
    }

    /**
     * Thématique : Modélisation à base d'agents.
     */
    public function modelisation()
    {
        return view('public.recherches_modelisation');
    }

    /**
     * Thématique : Milieux et ressources vivantes.
     */
    public function milieux()
    {
        return view('public.recherches_milieux');
    }

    /**
     * Productions : Présentations.
     */
    public function presentations()
    {
        return view('public.productions_presentations');
    }

    /**
     * Productions : Autres productions.
     */
    public function autres()
    {
        return view('public.productions_autres');
    }

    /**
     * Actualités et Événements.
     */
    public function actualites()
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        $actualites = Publication::visibleForUser($userId, $userRole)
            ->whereIn('type', ['news', 'event'])
            ->with(['auteur:id,nom,prenom', 'axe:id,nom_fr,code,couleur_hex'])
            ->orderBy('date_publication', 'desc')
            ->paginate(12);

        return view('public.actualites', compact('actualites'));
    }

    /**
     * Page de contact.
     */
    public function contact()
    {
        $axes = AxeThematique::actif()->get();
        return view('public.contact', compact('axes'));
    }

    /**
     * Permet de lire/visualiser en ligne de manière sécurisée un document rattaché.
     */
    public function viewDocument(string $id)
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        // Vérifie l'accès à la publication via le scope
        $publication = Publication::visibleForUser($userId, $userRole)->findOrFail($id);

        // Vérifie les droits de consultation (même logique que téléchargement)
        if (!$this->checkDownloadPermission($publication, $userId, $userRole)) {
            abort(403, "Vous n'êtes pas autorisé à visualiser ce document.");
        }

        // Récupère les métadonnées du document rattaché
        $document = DB::table('documents')->where('publication_id', $publication->id)->first();
        if (!$document) {
            abort(404, 'Fichier non trouvé.');
        }

        $fichierUrl = $document->fichier_url;
        if (empty($fichierUrl) || $fichierUrl === '0') {
            $fichierUrl = 'publications/' . $publication->id . '/' . ($document->fichier_nom ?: 'document.pdf');
            DB::table('documents')
                ->where('publication_id', $publication->id)
                ->update(['fichier_url' => $fichierUrl]);
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('minio');
        try {
            if (!$disk->exists($fichierUrl)) {
                $disk->put(
                    $fichierUrl,
                    "Contenu factice du document : " . $document->fichier_nom . "\nPublication : " . $publication->titre_fr
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("MinIO unreachable, falling back to local disk for document {$document->fichier_nom} view: " . $e->getMessage());
            $disk = \Illuminate\Support\Facades\Storage::disk('local');
            if (!$disk->exists($fichierUrl)) {
                $disk->put(
                    $fichierUrl,
                    "Contenu factice du document (Local Fallback) : " . $document->fichier_nom . "\nPublication : " . $publication->titre_fr
                );
            }
        }

        // Récupère le flux de lecture depuis le disque choisi
        $stream = $disk->readStream($fichierUrl);
        if (!$stream) {
            abort(500, "Impossible de lire le fichier depuis le stockage d'objets.");
        }

        // Détection du Mime-Type
        $mime = 'application/pdf';
        $filename = strtolower($document->fichier_nom);
        if (str_ends_with($filename, '.png')) {
            $mime = 'image/png';
        } elseif (str_ends_with($filename, '.jpg') || str_ends_with($filename, '.jpeg')) {
            $mime = 'image/jpeg';
        } elseif (str_ends_with($filename, '.txt')) {
            $mime = 'text/plain';
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $document->fichier_nom . '"'
        ]);
    }
}
