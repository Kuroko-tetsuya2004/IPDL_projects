<?php

namespace App\Modules\Search\Controllers;

use App\Modules\Content\Models\Publication;
use App\Modules\User\Models\AxeThematique;
use App\Modules\Integration\Services\AcademicSearchAggregator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request, AcademicSearchAggregator $aggregator): Response
    {
        $query  = $request->input('q', '');
        $type   = $request->input('type');
        $axeId  = $request->input('axe');
        $results = collect();
        $total   = 0;
        $externalResults = [];
        $source = 'local';

        if (strlen($query) >= 2) {
            $userId   = session('user_id');
            $userRole = session('user_role');

            $q = Publication::visibleForUser($userId, $userRole)
                ->with(['auteur:id,nom,prenom', 'axe:id,nom_fr,code,couleur_hex']);

            // Détection du type de requête (ORCID, DOI ou texte)
            $isOrcid = preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[0-9X]$/', $query);
            $isDoi = preg_match('/^10\.\d{4,9}\/[-._;()\/:A-Z0-9]+$/i', $query);

            if ($isDoi) {
                // Recherche exacte par DOI
                $q->where('doi', 'ilike', "%{$query}%");
            } elseif ($isOrcid) {
                // Recherche par ORCID de l'auteur
                $q->whereHas('auteur', function($sq) use ($query) {
                    $sq->where('orcid_id', $query);
                });
            } else {
                // Recherche plein texte classique
                $q->where(function ($sq) use ($query) {
                    $sq->where('titre_fr', 'ilike', "%{$query}%")
                       ->orWhere('resume_fr', 'ilike', "%{$query}%")
                       ->orWhere('titre_en', 'ilike', "%{$query}%")
                       ->orWhere('mots_cles', 'ilike', "%{$query}%");
                });
            }

            if ($type)  $q->where('type', $type);
            if ($axeId) $q->where('axe_id', $axeId);

            $paginated = $q->orderBy('date_publication', 'desc')->paginate(15)->withQueryString();
            $results   = $paginated;
            $total     = $paginated->total();

            // Fallback dynamique vers OpenAlex / API externe si aucun résultat local
            if ($total === 0) {
                $source = 'openalex';
                try {
                    $externalResults = $aggregator->search($query, 15);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Erreur fallback recherche externe: " . $e->getMessage());
                    $externalResults = [];
                }
            }
        }

        $axes  = AxeThematique::actif()->get(['id', 'nom_fr', 'code']);
        $types = [
            'article'      => 'Articles',
            'news'         => 'Actualités',
            'event'        => 'Événements',
            'thesis'       => 'Thèses',
            'report'       => 'Rapports',
            'dataset'      => 'Datasets',
            'presentation' => 'Présentations',
        ];

        return Inertia::render('Search/Results', [
            'query'   => $query,
            'results' => $results,
            'total'   => $total,
            'externalResults' => $externalResults,
            'source'  => $source,
            'axes'    => $axes,
            'types'   => $types,
            'filters' => $request->only(['q', 'type', 'axe']),
        ]);
    }
}
