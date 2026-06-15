<?php

namespace App\Modules\Integration\Controllers;

use Illuminate\Routing\Controller;
use App\Modules\Integration\Models\ExternalPublication;
use App\Modules\Integration\Services\PublicationImportService;
use App\Modules\Content\Models\Publication;
use App\Modules\Content\Models\WorkflowValidation;
use App\Modules\User\Models\User;
use App\Modules\User\Models\AxeThematique;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * ImportController
 *
 * Deux rôles distincts :
 * 1. Exposition des publications EXTERNES (lecture seule) pour le portail public et la recherche
 * 2. Supervision admin du processus d'import automatique
 *
 * NB : Les chercheurs UMMISCO soumettent leurs propres articles via
 *      le workflow publications existant (PublicationController).
 */
class ImportController extends Controller
{
    public function __construct(private PublicationImportService $importer) {}

    // ──────────────────────────────────────────────────────────────────────────
    // PORTAIL PUBLIC — lecture seule des articles externes
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Liste paginée des articles externes (portail public)
     * GET /publications/externes
     */
    public function publicIndex(Request $request)
    {
        $query  = $request->get('q', '');
        $source = $request->get('source', '');
        $annee  = $request->get('annee', '');
        $perPage = 20;

        $articles = ExternalPublication::query()
            ->where('statut', ExternalPublication::STATUT_DISPONIBLE)
            ->when($query, fn($q) => $q->where(fn($sq) =>
                $sq->where('titre', 'ilike', "%{$query}%")
                   ->orWhere('resume', 'ilike', "%{$query}%")
                   ->orWhere('auteurs', 'ilike', "%{$query}%")
            ))
            ->when($source, fn($q) => $q->where('source', $source))
            ->when($annee,  fn($q) => $q->where('annee', $annee))
            ->orderByDesc('annee')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        // Années disponibles pour le filtre
        $availableYears = ExternalPublication::where('statut', ExternalPublication::STATUT_DISPONIBLE)
            ->whereNotNull('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee');

        // Stats par source
        $sourceStats = ExternalPublication::where('statut', ExternalPublication::STATUT_DISPONIBLE)
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        $axes = [];
        if (session('user_id')) {
            $axes = AxeThematique::actif()->get(['id', 'nom_fr', 'code']);
        }

        return Inertia::render('PublicPortal/ExternalPublications', [
            'articles'       => $articles,
            'filters'        => compact('query', 'source', 'annee'),
            'availableYears' => $availableYears,
            'sourceStats'    => $sourceStats,
            'axes'           => $axes,
        ]);
    }

    /**
     * Détail d'un article externe
     * GET /publications/externes/{id}
     */
    public function publicShow(string $id)
    {
        $article = ExternalPublication::where('statut', ExternalPublication::STATUT_DISPONIBLE)
            ->findOrFail($id);

        return Inertia::render('PublicPortal/ExternalPublicationShow', [
            'article' => $article,
        ]);
    }

    /**
     * API JSON pour la recherche live (barre de recherche)
     * GET /api/publications/externes/search?q=...
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = ExternalPublication::where('statut', ExternalPublication::STATUT_DISPONIBLE)
            ->where(fn($query) =>
                $query->where('titre', 'ilike', "%{$q}%")
                      ->orWhere('auteurs', 'ilike', "%{$q}%")
            )
            ->select('id', 'source', 'titre', 'auteurs', 'annee', 'doi', 'pdf_url')
            ->orderByDesc('annee')
            ->limit(10)
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'source'      => $a->source,
                'source_label'=> $a->source_label,
                'titre'       => $a->titre,
                'auteurs'     => $a->auteurs_array,
                'annee'       => $a->annee,
                'doi'         => $a->doi,
                'pdf_url'     => $a->pdf_url,
                'external_url'=> $this->buildExternalUrl($a),
            ]);

        return response()->json($results);
    }

    /**
     * Utilisateurs connectés : fetch en direct sur les bases mondiales
     * POST /publications/externes/fetch-live
     */
    public function userLiveFetch(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:200',
        ]);

        $this->importer->fetchAndStore($request->input('q'), 'all', 20);

        return back()->with('success', 'La recherche mondiale a été effectuée. Les résultats sont mis à jour.');
    }

    /**
     * Utilisateurs connectés : importer une publication externe dans son profil
     * POST /publications/externes/{id}/import
     */
    public function importToProfile(Request $request, string $id)
    {
        $request->validate([
            'axe_id' => 'required|uuid|exists:axes_thematiques,id',
            'type'   => 'nullable|string',
        ]);

        $userId = session('user_id');
        $user = User::findOrFail($userId);

        $external = ExternalPublication::where('statut', ExternalPublication::STATUT_DISPONIBLE)->findOrFail($id);

        $motsCles = null;
        if (isset($external->raw_data['keywords'])) {
            $motsCles = is_array($external->raw_data['keywords']) ? $external->raw_data['keywords'] : explode(',', $external->raw_data['keywords']);
        } elseif (isset($external->raw_data['concepts'])) {
            // OpenAlex concepts
            $motsCles = collect($external->raw_data['concepts'])->pluck('display_name')->take(5)->toArray();
        }

        $typePub = $request->input('type') ?? $external->type_publication ?? 'article';
        $validTypes = ['article','document','event','dataset','news','thesis','report','presentation'];
        if (!in_array($typePub, $validTypes)) {
            $typePub = 'article';
        }

        \DB::transaction(function () use ($user, $external, $request, $typePub, $motsCles) {
            $publication = Publication::create([
                'titre_fr'           => mb_substr($external->titre ?? 'Sans titre', 0, 500),
                'resume_fr'          => $external->resume ?? 'Résumé non disponible.',
                'type'               => $typePub,
                'statut'             => $user->requiresWorkflow() ? Publication::STATUS_SUBMITTED : Publication::STATUS_PUBLISHED,
                'visibilite'         => 'public',
                'langue_principale'  => 'fr',
                'auteur_id'          => $user->id,
                'axe_id'             => $request->input('axe_id'),
                'mots_cles'          => $motsCles,
                'date_soumission'    => now(),
                'date_publication'   => $user->canPublishDirectly() ? ($external->annee ? $external->annee.'-01-01' : now()) : null,
                'commentaire_auteur' => 'Importé depuis ' . ucfirst($external->source) . ' (ID: ' . $external->external_id . ')',
            ]);

            // Save PDF URL if available
            if ($external->pdf_url) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(15)->get($external->pdf_url);
                    if ($response->successful()) {
                        $filename = 'imported_' . \Illuminate\Support\Str::random(10) . '.pdf';
                        $path = 'documents/' . $publication->id . '/' . $filename;
                        \Illuminate\Support\Facades\Storage::disk('minio')->put($path, $response->body());
                        
                        \DB::table('documents')->insert([
                            'publication_id' => $publication->id,
                            'fichier_nom' => $filename,
                            'fichier_taille' => strlen($response->body()),
                            'fichier_mime' => 'application/pdf',
                            'fichier_url' => $path,
                        ]);
                    } else {
                        $publication->resume_fr .= "\n\nLien PDF : " . $external->pdf_url;
                        $publication->save();
                    }
                } catch (\Exception $e) {
                    $publication->resume_fr .= "\n\nLien PDF : " . $external->pdf_url;
                    $publication->save();
                    \Illuminate\Support\Facades\Log::warning("Impossible de télécharger le PDF : " . $e->getMessage());
                }
            }
            if ($external->doi) {
                if (!str_contains($publication->resume_fr, 'DOI :')) {
                    $publication->resume_fr .= "\nDOI : " . $external->doi;
                    $publication->save();
                }
            }

            if ($user->requiresWorkflow()) {
                $delaiJours = (int) \DB::table('parametres_systeme')->where('cle', 'workflow_delai_jours')->value('valeur') ?: 14;
                WorkflowValidation::create([
                    'publication_id'     => $publication->id,
                    'soumetteur_id'      => $user->id,
                    'statut'             => WorkflowValidation::STATUS_PENDING,
                    'version'            => 1,
                    'date_soumission'    => now(),
                    'date_limite'        => now()->addDays($delaiJours),
                ]);
            }
        });

        return back()->with('success', 'Publication importée avec succès dans votre profil.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ADMIN — supervision des imports (super_admin seulement)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Dashboard d'import admin
     * GET /admin/import
     */
    public function adminIndex()
    {
        $stats = [
            'total'          => ExternalPublication::count(),
            'disponible'     => ExternalPublication::where('statut', 'disponible')->count(),
            'par_source'     => ExternalPublication::selectRaw('source, COUNT(*) as total, MAX(fetched_at) as last_fetch')
                                    ->groupBy('source')
                                    ->get(),
            'derniers'       => ExternalPublication::latest('fetched_at')->take(10)->get(),
            'dernier_import' => ExternalPublication::max('fetched_at'),
        ];

        $logPath = storage_path('logs/publications-import.log');
        $importLog = file_exists($logPath)
            ? implode('', array_slice(file($logPath), -50))
            : 'Aucun import encore effectué.';

        return Inertia::render('Admin/Import/Index', [
            'stats'     => $stats,
            'importLog' => $importLog,
        ]);
    }

    /**
     * Déclenche un import manuel
     * POST /admin/import/run
     */
    public function adminRun(Request $request)
    {
        $request->validate([
            'query'  => 'required|string|min:2|max:200',
            'source' => 'in:all,semantic_scholar,openalex,arxiv',
            'limit'  => 'integer|min:5|max:200',
        ]);

        $stats = $this->importer->fetchAndStore(
            $request->input('query'),
            $request->input('source', 'all'),
            $request->input('limit', 50)
        );

        return back()->with('success', sprintf(
            '✅ Import terminé : %d articles récupérés, %d nouveaux, %d mis à jour.',
            $stats['fetched'], $stats['new'], $stats['updated']
        ));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** Construit le lien vers l'article sur sa plateforme d'origine */
    private function buildExternalUrl(ExternalPublication $article): string
    {
        return match ($article->source) {
            'semantic_scholar' => "https://www.semanticscholar.org/paper/{$article->external_id}",
            'openalex'         => "https://openalex.org/works/{$article->external_id}",
            'arxiv'            => "https://arxiv.org/abs/{$article->external_id}",
            default            => $article->doi ? "https://doi.org/{$article->doi}" : '#',
        };
    }
}
