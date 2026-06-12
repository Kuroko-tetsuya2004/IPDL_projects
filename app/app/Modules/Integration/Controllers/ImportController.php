<?php

namespace App\Modules\Integration\Controllers;

use Illuminate\Routing\Controller;
use App\Modules\Integration\Models\ExternalPublication;
use App\Modules\Integration\Services\PublicationImportService;
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

        return Inertia::render('PublicPortal/ExternalPublications', [
            'articles'       => $articles,
            'filters'        => compact('query', 'source', 'annee'),
            'availableYears' => $availableYears,
            'sourceStats'    => $sourceStats,
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
