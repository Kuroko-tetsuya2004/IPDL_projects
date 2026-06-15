<?php

namespace App\Modules\Integration\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Modules\Integration\Services\AcademicSearchAggregator;

class ApiSearchController extends Controller
{
    /**
     * Recherche dans les sources externes (OpenAlex, Semantic Scholar, CORE, Crossref)
     * et retourne les résultats unifiés.
     */
    public function search(Request $request, AcademicSearchAggregator $aggregator)
    {
        $query = $request->get('q');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Requête de recherche invalide (minimum 2 caractères requis).',
                'data' => []
            ], 400);
        }

        $limit = (int) $request->get('limit', 20);
        if ($limit > 50) $limit = 50;

        try {
            $results = $aggregator->search($query, $limit);

            return response()->json([
                'success' => true,
                'count' => count($results),
                'data' => $results
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("[ApiSearchController] Erreur : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'agrégation des recherches.',
                'data' => []
            ], 500);
        }
    }
}
