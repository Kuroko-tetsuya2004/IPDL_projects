<?php

namespace App\Modules\Search\Controllers;

use App\Modules\Content\Models\Publication;
use App\Modules\User\Models\AxeThematique;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $query  = $request->input('q', '');
        $type   = $request->input('type');
        $axeId  = $request->input('axe');
        $results = collect();
        $total   = 0;

        if (strlen($query) >= 2) {
            $userId   = session('user_id');
            $userRole = session('user_role');

            $q = Publication::visibleForUser($userId, $userRole)
                ->with(['auteur:id,nom,prenom', 'axe:id,nom_fr,code,couleur_hex']);

            // Full-text search
            if ($query) {
                $lang = 'fr';
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
            'axes'    => $axes,
            'types'   => $types,
            'filters' => $request->only(['q', 'type', 'axe']),
        ]);
    }
}
