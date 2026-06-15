<?php

namespace App\Modules\Integration\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AcademicSearchAggregator
{
    /**
     * Effectue une recherche en parallèle sur plusieurs sources académiques.
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 20): array
    {
        $openAlexEmail = env('OPENALEX_EMAIL', 'default@example.com');
        $semanticScholarApiKey = env('SEMANTIC_SCHOLAR_API_KEY');
        $coreApiKey = env('CORE_API_KEY');
        $crossrefMailto = env('CROSSREF_MAILTO', 'default@example.com');

        $encodedQuery = urlencode($query);

        // Run requests in parallel using Http::pool
        $responses = Http::pool(function ($pool) use ($encodedQuery, $limit, $openAlexEmail, $semanticScholarApiKey, $coreApiKey, $crossrefMailto) {
            $pool->as('openalex')->timeout(10)->get("https://api.openalex.org/works?search={$encodedQuery}&mailto={$openAlexEmail}&per-page={$limit}");
            
            $ssReq = $pool->as('semanticscholar')->timeout(10);
            if ($semanticScholarApiKey) {
                $ssReq->withHeaders(['x-api-key' => $semanticScholarApiKey]);
            }
            $ssReq->get("https://api.semanticscholar.org/graph/v1/paper/search?query={$encodedQuery}&fields=title,authors,year,abstract,openAccessPdf,externalIds&limit={$limit}");
            
            $coreReq = $pool->as('core')->timeout(10);
            if ($coreApiKey) {
                $coreReq->withToken($coreApiKey);
            }
            $coreReq->get("https://api.core.ac.uk/v3/search/works?q={$encodedQuery}&limit={$limit}");
            
            $pool->as('crossref')->timeout(10)->get("https://api.crossref.org/works?query={$encodedQuery}&rows={$limit}&mailto={$crossrefMailto}");
        });

        $results = [];

        // Parse OpenAlex
        if (isset($responses['openalex']) && $responses['openalex']->successful()) {
            $data = $responses['openalex']->json();
            if (!empty($data['results'])) {
                foreach ($data['results'] as $item) {
                    $results[] = $this->normalizeOpenAlex($item);
                }
            }
        } else {
            Log::warning("[AcademicSearch] OpenAlex request failed or timed out.");
        }

        // Parse Semantic Scholar
        if (isset($responses['semanticscholar']) && $responses['semanticscholar']->successful()) {
            $data = $responses['semanticscholar']->json();
            if (!empty($data['data'])) {
                foreach ($data['data'] as $item) {
                    $results[] = $this->normalizeSemanticScholar($item);
                }
            }
        } else {
            Log::warning("[AcademicSearch] Semantic Scholar request failed or timed out.");
        }

        // Parse CORE
        if (isset($responses['core']) && $responses['core']->successful()) {
            $data = $responses['core']->json();
            if (!empty($data['results'])) {
                foreach ($data['results'] as $item) {
                    $results[] = $this->normalizeCore($item);
                }
            }
        } else {
            Log::warning("[AcademicSearch] CORE request failed or timed out.");
        }

        // Parse Crossref
        if (isset($responses['crossref']) && $responses['crossref']->successful()) {
            $data = $responses['crossref']->json();
            if (!empty($data['message']['items'])) {
                foreach ($data['message']['items'] as $item) {
                    $results[] = $this->normalizeCrossref($item);
                }
            }
        } else {
            Log::warning("[AcademicSearch] CrossRef request failed or timed out.");
        }

        return $this->deduplicate($results);
    }

    private function normalizeOpenAlex(array $item): array
    {
        $authors = array_map(fn($a) => $a['author']['display_name'] ?? '', $item['authorships'] ?? []);
        $doi = $item['doi'] ? str_replace('https://doi.org/', '', $item['doi']) : null;
        
        return [
            'id' => $item['id'],
            'title' => $item['title'] ?? 'Sans titre',
            'authors' => array_values(array_filter($authors)),
            'year' => $item['publication_year'] ?? null,
            'abstract' => null, // OpenAlex fournit un index inversé difficile à parser pour de l'affichage rapide
            'type' => $item['type'] ?? 'article',
            'source' => 'OpenAlex',
            'doi' => $doi,
            'pdfUrl' => $item['open_access']['oa_url'] ?? null,
            'openAccess' => $item['open_access']['is_oa'] ?? false,
        ];
    }

    private function normalizeSemanticScholar(array $item): array
    {
        $authors = array_map(fn($a) => $a['name'] ?? '', $item['authors'] ?? []);
        
        return [
            'id' => $item['paperId'],
            'title' => $item['title'] ?? 'Sans titre',
            'authors' => array_values(array_filter($authors)),
            'year' => $item['year'] ?? null,
            'abstract' => $item['abstract'] ?? null,
            'type' => 'article',
            'source' => 'Semantic Scholar',
            'doi' => $item['externalIds']['DOI'] ?? null,
            'pdfUrl' => $item['openAccessPdf']['url'] ?? null,
            'openAccess' => !empty($item['openAccessPdf']),
        ];
    }

    private function normalizeCore(array $item): array
    {
        $authors = array_map(fn($a) => $a['name'] ?? '', $item['authors'] ?? []);
        
        return [
            'id' => $item['id'],
            'title' => $item['title'] ?? 'Sans titre',
            'authors' => array_values(array_filter($authors)),
            'year' => $item['yearPublished'] ?? null,
            'abstract' => $item['abstract'] ?? null,
            'type' => 'article',
            'source' => 'CORE',
            'doi' => $item['doi'] ?? null,
            'pdfUrl' => $item['downloadUrl'] ?? null,
            'openAccess' => true,
        ];
    }

    private function normalizeCrossref(array $item): array
    {
        $authors = [];
        if (!empty($item['author'])) {
            foreach ($item['author'] as $a) {
                $authors[] = trim(($a['given'] ?? '') . ' ' . ($a['family'] ?? ''));
            }
        }
        
        $pdfUrl = null;
        if (!empty($item['link'])) {
            foreach ($item['link'] as $link) {
                if (($link['content-type'] ?? '') === 'application/pdf') {
                    $pdfUrl = $link['URL'];
                    break;
                }
            }
        }

        return [
            'id' => $item['DOI'] ?? uniqid(),
            'title' => !empty($item['title']) ? $item['title'][0] : 'Sans titre',
            'authors' => array_values(array_filter($authors)),
            'year' => $item['published-print']['date-parts'][0][0] ?? $item['published-online']['date-parts'][0][0] ?? null,
            'abstract' => $item['abstract'] ?? null,
            'type' => $item['type'] ?? 'article',
            'source' => 'CrossRef',
            'doi' => $item['DOI'] ?? null,
            'pdfUrl' => $pdfUrl,
            'openAccess' => !empty($pdfUrl),
        ];
    }

    /**
     * Déduplique la liste des résultats.
     */
    private function deduplicate(array $items): array
    {
        $deduplicated = [];
        $seenDois = [];
        $seenTitles = [];

        foreach ($items as $item) {
            $isDuplicate = false;

            // Déduplication par DOI
            if (!empty($item['doi'])) {
                $doi = strtolower(trim($item['doi']));
                if (isset($seenDois[$doi])) {
                    $isDuplicate = true;
                    // On conserve le lien PDF si la source dupliquée en a un
                    if ($item['pdfUrl'] && !$deduplicated[$seenDois[$doi]]['pdfUrl']) {
                        $deduplicated[$seenDois[$doi]]['pdfUrl'] = $item['pdfUrl'];
                        $deduplicated[$seenDois[$doi]]['openAccess'] = true;
                    }
                } else {
                    $seenDois[$doi] = count($deduplicated);
                }
            }

            // Déduplication par Titre
            $titleKey = strtolower(preg_replace('/[^a-z0-9]/', '', $item['title']));
            if (!$isDuplicate && $titleKey) {
                if (isset($seenTitles[$titleKey])) {
                    $isDuplicate = true;
                    if ($item['pdfUrl'] && !$deduplicated[$seenTitles[$titleKey]]['pdfUrl']) {
                        $deduplicated[$seenTitles[$titleKey]]['pdfUrl'] = $item['pdfUrl'];
                        $deduplicated[$seenTitles[$titleKey]]['openAccess'] = true;
                    }
                } else {
                    $seenTitles[$titleKey] = count($deduplicated);
                }
            }

            if (!$isDuplicate) {
                $deduplicated[] = $item;
            }
        }

        return $deduplicated;
    }
}
