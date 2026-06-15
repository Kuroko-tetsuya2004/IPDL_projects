<?php

namespace App\Modules\Integration\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

/**
 * ArxivService
 * API gratuite Atom/XML — http://export.arxiv.org/api/query
 * Pas de clé API requise — limite 3 req/sec
 */
class ArxivService
{
    private const BASE_URL = 'http://export.arxiv.org/api/query';

    /**
     * Recherche par mots-clés dans tous les champs
     *
     * @param  string  $query       Requête (ex: "agent-based model epidemiology")
     * @param  int     $maxResults  Max 2000 selon les règles arXiv
     * @param  int     $start       Offset pour la pagination
     * @return array                Articles normalisés
     */
    public function search(string $query, int $maxResults = 50, int $start = 0): array
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders(['Accept' => 'application/atom+xml'])
                ->get(self::BASE_URL, [
                    'search_query' => 'all:' . $query,
                    'start'        => $start,
                    'max_results'  => min($maxResults, 2000),
                    'sortBy'       => 'submittedDate',
                    'sortOrder'    => 'descending',
                ]);

            if (!$response->successful()) {
                Log::warning('[arXiv] Réponse non-200', ['status' => $response->status()]);
                return [];
            }

            return $this->parseAtom($response->body());

        } catch (ConnectionException $e) {
            Log::error('[arXiv] Connexion impossible', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Recherche par auteur (champ au: dans arXiv)
     */
    public function searchByAuthor(string $authorName, int $maxResults = 50): array
    {
        // arXiv utilise le format: au:Lastname_F
        $formattedName = str_replace(' ', '_', $authorName);
        return $this->search("au:{$formattedName}", $maxResults);
    }

    /**
     * Parse le flux Atom XML retourné par arXiv
     */
    private function parseAtom(string $xml): array
    {
        try {
            // Supprime uniquement le namespace par défaut (Atom) pour simplifier le parsing des tags standards
            $xml = preg_replace('/xmlns="[^"]+"/', '', $xml);
            $feed = new SimpleXMLElement($xml);

            $results = [];
            foreach ($feed->entry ?? [] as $entry) {
                $normalized = $this->normalizeEntry($entry);
                if ($normalized) {
                    $results[] = $normalized;
                }
            }
            return $results;

        } catch (\Exception $e) {
            Log::error('[arXiv] Erreur parsing XML', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Normalise un élément entry arXiv
     */
    private function normalizeEntry(SimpleXMLElement $entry): ?array
    {
        $id = (string) $entry->id;
        if (!$id) {
            return null;
        }

        // Extrait l'arxiv ID (ex: 2301.12345)
        $arxivId = basename(parse_url($id, PHP_URL_PATH));
        $arxivId = preg_replace('/v\d+$/', '', $arxivId); // Retire la version

        // DOI (souvent dans les links)
        $doi = null;
        foreach ($entry->link ?? [] as $link) {
            $attrs = $link->attributes();
            if ((string) ($attrs['title'] ?? '') === 'doi') {
                $href = (string) ($attrs['href'] ?? '');
                $doi  = str_replace('http://dx.doi.org/', '', $href);
                $doi  = str_replace('https://doi.org/', '', $doi);
            }
        }

        // PDF URL
        $pdfUrl = "https://arxiv.org/pdf/{$arxivId}.pdf";

        // Auteurs
        $authors = [];
        foreach ($entry->author ?? [] as $author) {
            $name = (string) ($author->name ?? '');
            if ($name) {
                $authors[] = $name;
            }
        }

        // Année depuis la date de publication
        $published = (string) ($entry->published ?? '');
        $annee     = $published ? substr($published, 0, 4) : '';

        return [
            'source'          => 'arxiv',
            'external_id'     => $arxivId,
            'doi'             => $doi,
            'titre'           => trim((string) ($entry->title ?? '')),
            'resume'          => trim((string) ($entry->summary ?? '')),
            'auteurs'         => json_encode($authors),
            'annee'           => $annee,
            'journal'         => 'arXiv preprint',
            'type_publication'=> 'preprint',
            'pdf_url'         => $pdfUrl,
            'raw_data'        => [
                'id'        => $id,
                'arxiv_id'  => $arxivId,
                'title'     => (string) $entry->title,
                'published' => $published,
                'authors'   => $authors,
                'doi'       => $doi,
            ],
        ];
    }
}
