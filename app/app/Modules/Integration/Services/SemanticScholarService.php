<?php

namespace App\Modules\Integration\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SemanticScholarService
 * API gratuite — https://api.semanticscholar.org/graph/v1/
 * Pas de clé API requise (rate limit 100 req/5min)
 */
class SemanticScholarService
{
    private const BASE_URL = 'https://api.semanticscholar.org/graph/v1';

    // Champs demandés à l'API
    private const PAPER_FIELDS = 'paperId,title,abstract,year,authors,externalIds,openAccessPdf,publicationTypes,journal,publicationDate';

    /**
     * Recherche par mots-clés
     *
     * @param  string  $query   Requête de recherche
     * @param  int     $limit   Nombre max de résultats (max 100)
     * @param  int     $offset  Pour la pagination
     * @return array            Liste d'articles normalisés
     */
    public function searchByQuery(string $query, int $limit = 50, int $offset = 0): array
    {
        try {
            $response = Http::timeout(15)
                ->get(self::BASE_URL . '/paper/search', [
                    'query'  => $query,
                    'limit'  => min($limit, 100),
                    'offset' => $offset,
                    'fields' => self::PAPER_FIELDS,
                ]);

            if (!$response->successful()) {
                Log::warning('[SemanticScholar] Réponse non-200', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json();
            return $this->normalizeMany($data['data'] ?? []);

        } catch (ConnectionException $e) {
            Log::error('[SemanticScholar] Connexion impossible', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Recherche par nom d'auteur
     */
    public function searchByAuthor(string $authorName, int $limit = 50): array
    {
        try {
            // Cherche l'auteur d'abord
            $authorResponse = Http::timeout(15)
                ->get(self::BASE_URL . '/author/search', [
                    'query'  => $authorName,
                    'limit'  => 5,
                    'fields' => 'authorId,name,paperCount,hIndex',
                ]);

            if (!$authorResponse->successful()) {
                return [];
            }

            $authors = $authorResponse->json('data', []);
            if (empty($authors)) {
                return [];
            }

            // Prend le premier auteur et récupère ses articles
            $authorId = $authors[0]['authorId'];
            return $this->searchByAuthorId($authorId, $limit);

        } catch (ConnectionException $e) {
            Log::error('[SemanticScholar] Erreur searchByAuthor', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Récupère les articles d'un auteur par son ID Semantic Scholar
     */
    public function searchByAuthorId(string $authorId, int $limit = 50): array
    {
        try {
            $response = Http::timeout(15)
                ->get(self::BASE_URL . "/author/{$authorId}/papers", [
                    'limit'  => min($limit, 1000),
                    'fields' => self::PAPER_FIELDS,
                ]);

            if (!$response->successful()) {
                return [];
            }

            return $this->normalizeMany($response->json('data', []));

        } catch (ConnectionException $e) {
            Log::error('[SemanticScholar] Erreur searchByAuthorId', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Normalise un tableau de résultats bruts
     */
    private function normalizeMany(array $papers): array
    {
        return array_values(array_filter(array_map(
            fn($paper) => $this->normalize($paper),
            $papers
        )));
    }

    /**
     * Normalise un article brut en format commun
     */
    public function normalize(array $paper): ?array
    {
        if (empty($paper['paperId'])) {
            return null;
        }

        $doi = $paper['externalIds']['DOI'] ?? null;

        $authors = array_map(
            fn($a) => $a['name'] ?? '',
            $paper['authors'] ?? []
        );

        return [
            'source'          => 'semantic_scholar',
            'external_id'     => $paper['paperId'],
            'doi'             => $doi,
            'titre'           => $paper['title'] ?? null,
            'resume'          => $paper['abstract'] ?? null,
            'auteurs'         => json_encode($authors),
            'annee'           => (string) ($paper['year'] ?? ''),
            'journal'         => $paper['journal']['name'] ?? null,
            'type_publication'=> 'article',
            'pdf_url'         => $paper['openAccessPdf']['url'] ?? null,
            'raw_data'        => $paper,
        ];
    }
}
