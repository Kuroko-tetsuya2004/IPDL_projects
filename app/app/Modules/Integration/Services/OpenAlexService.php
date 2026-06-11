<?php

namespace App\Modules\Integration\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAlexService
 * API gratuite — https://api.openalex.org/
 * Polite pool (email requis) : 100k req/jour
 */
class OpenAlexService
{
    private const BASE_URL = 'https://api.openalex.org';

    // Email d'identification pour le "polite pool" (plus rapide)
    private string $email;

    public function __construct()
    {
        $this->email = config('services.openalex.email', 'contact@ummisco.sn');
    }

    /**
     * Recherche par mots-clés dans les titres/résumés
     */
    public function searchByQuery(string $query, int $limit = 50, int $page = 1): array
    {
        try {
            $response = Http::timeout(15)
                ->get(self::BASE_URL . '/works', [
                    'search'     => $query,
                    'per-page'   => min($limit, 200),
                    'page'       => $page,
                    'filter'     => 'is_retracted:false',
                    'select'     => 'id,doi,title,abstract_inverted_index,publication_year,authorships,primary_location,open_access,type,biblio',
                    'mailto'     => $this->email,
                ]);

            if (!$response->successful()) {
                Log::warning('[OpenAlex] Réponse non-200', ['status' => $response->status()]);
                return [];
            }

            $results = $response->json('results', []);
            return $this->normalizeMany($results);

        } catch (ConnectionException $e) {
            Log::error('[OpenAlex] Connexion impossible', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Recherche par affiliation (ROR ID ou nom d'institution)
     */
    public function searchByAffiliation(string $affiliation, int $limit = 100): array
    {
        try {
            $response = Http::timeout(15)
                ->get(self::BASE_URL . '/works', [
                    'filter'   => "institutions.display_name.search:{$affiliation},is_retracted:false",
                    'per-page' => min($limit, 200),
                    'select'   => 'id,doi,title,abstract_inverted_index,publication_year,authorships,primary_location,open_access,type',
                    'mailto'   => $this->email,
                    'sort'     => 'publication_year:desc',
                ]);

            if (!$response->successful()) {
                return [];
            }

            return $this->normalizeMany($response->json('results', []));

        } catch (ConnectionException $e) {
            Log::error('[OpenAlex] Erreur searchByAffiliation', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Normalise un tableau d'articles
     */
    private function normalizeMany(array $works): array
    {
        return array_values(array_filter(array_map(
            fn($w) => $this->normalize($w),
            $works
        )));
    }

    /**
     * Reconstruit le résumé depuis l'index inversé OpenAlex
     */
    private function rebuildAbstract(?array $invertedIndex): ?string
    {
        if (empty($invertedIndex)) {
            return null;
        }

        $words = [];
        foreach ($invertedIndex as $word => $positions) {
            foreach ($positions as $pos) {
                $words[$pos] = $word;
            }
        }
        ksort($words);
        return implode(' ', $words);
    }

    /**
     * Normalise un article OpenAlex en format commun
     */
    public function normalize(array $work): ?array
    {
        $externalId = $work['id'] ?? null;
        if (!$externalId) {
            return null;
        }

        // Extrait l'ID court (ex: W2741809809)
        $shortId = basename($externalId);

        $doi = $work['doi'] ?? null;
        if ($doi) {
            $doi = str_replace('https://doi.org/', '', $doi);
        }

        // Auteurs
        $authors = array_map(
            fn($a) => $a['author']['display_name'] ?? '',
            $work['authorships'] ?? []
        );

        // PDF en accès ouvert
        $pdfUrl = $work['open_access']['oa_url'] ?? null;

        // Journal
        $journal = $work['primary_location']['source']['display_name'] ?? null;

        return [
            'source'          => 'openalex',
            'external_id'     => $shortId,
            'doi'             => $doi,
            'titre'           => $work['title'] ?? null,
            'resume'          => $this->rebuildAbstract($work['abstract_inverted_index'] ?? null),
            'auteurs'         => json_encode($authors),
            'annee'           => (string) ($work['publication_year'] ?? ''),
            'journal'         => $journal,
            'type_publication'=> $work['type'] ?? 'article',
            'pdf_url'         => $pdfUrl,
            'raw_data'        => $work,
        ];
    }
}
