<?php

namespace App\Modules\Integration\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CrossrefService
 * API gratuite — https://api.crossref.org/works
 * Polite pool (email requis)
 */
class CrossrefService
{
    private const BASE_URL = 'https://api.crossref.org';

    private string $email;

    public function __construct()
    {
        $this->email = config('services.crossref.email', 'contact@ummisco.sn');
    }

    /**
     * Recherche par mots-clés
     */
    public function searchByQuery(string $query, int $limit = 50, int $offset = 0): array
    {
        try {
            $response = Http::timeout(15)
                ->get(self::BASE_URL . '/works', [
                    'query'  => $query,
                    'rows'   => min($limit, 100),
                    'offset' => $offset,
                    'mailto' => $this->email,
                    'select' => 'DOI,title,abstract,author,published-print,published-online,container-title,type,link'
                ]);

            if (!$response->successful()) {
                Log::warning('[Crossref] Réponse non-200', ['status' => $response->status()]);
                return [];
            }

            $items = $response->json('message.items', []);
            return $this->normalizeMany($items);

        } catch (ConnectionException $e) {
            Log::error('[Crossref] Connexion impossible', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Recherche par auteur (query.author)
     */
    public function searchByAuthor(string $authorName, int $limit = 50): array
    {
        try {
            $response = Http::timeout(15)
                ->get(self::BASE_URL . '/works', [
                    'query.author' => $authorName,
                    'rows'         => min($limit, 100),
                    'mailto'       => $this->email,
                    'select'       => 'DOI,title,abstract,author,published-print,published-online,container-title,type,link'
                ]);

            if (!$response->successful()) {
                return [];
            }

            return $this->normalizeMany($response->json('message.items', []));

        } catch (ConnectionException $e) {
            Log::error('[Crossref] Erreur searchByAuthor', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function normalizeMany(array $items): array
    {
        return array_values(array_filter(array_map(
            fn($i) => $this->normalize($i),
            $items
        )));
    }

    public function normalize(array $item): ?array
    {
        $doi = $item['DOI'] ?? null;
        if (!$doi) {
            return null;
        }

        // Auteurs
        $authors = [];
        foreach ($item['author'] ?? [] as $author) {
            $name = trim(($author['given'] ?? '') . ' ' . ($author['family'] ?? ''));
            if ($name) {
                $authors[] = $name;
            }
        }

        // Année
        $year = null;
        $published = $item['published-print'] ?? $item['published-online'] ?? null;
        if ($published && isset($published['date-parts'][0][0])) {
            $year = (string) $published['date-parts'][0][0];
        }

        // PDF dans les links Crossref ?
        $pdfUrl = null;
        foreach ($item['link'] ?? [] as $link) {
            if (isset($link['content-type']) && $link['content-type'] === 'application/pdf') {
                $pdfUrl = $link['URL'] ?? null;
                break;
            }
        }

        // Titre et Abstract (Crossref les renvoie souvent comme tableaux)
        $title = is_array($item['title'] ?? null) ? ($item['title'][0] ?? null) : ($item['title'] ?? null);
        $abstract = $item['abstract'] ?? null;
        // Enlève les balises JATS si présentes (ex: <jats:p>)
        if ($abstract) {
            $abstract = preg_replace('/<[^>]*>/', '', $abstract);
            $abstract = trim($abstract);
        }

        $journal = is_array($item['container-title'] ?? null) ? ($item['container-title'][0] ?? null) : ($item['container-title'] ?? null);

        return [
            'source'          => 'crossref',
            'external_id'     => $doi, // DOI sert d'ID externe pour CrossRef
            'doi'             => $doi,
            'titre'           => $title,
            'resume'          => $abstract,
            'auteurs'         => json_encode($authors),
            'annee'           => $year,
            'journal'         => $journal,
            'type_publication'=> $item['type'] ?? 'article',
            'pdf_url'         => $pdfUrl,
            'raw_data'        => $item,
        ];
    }
}
