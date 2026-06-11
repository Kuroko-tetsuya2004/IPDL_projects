<?php

namespace App\Modules\Integration\Services;

use App\Modules\Integration\Models\ExternalPublication;
use Illuminate\Support\Facades\Log;

/**
 * PublicationImportService
 *
 * Orchestre le fetch depuis les 4 sources et persiste dans external_publications.
 * Les articles externes sont en LECTURE SEULE — ils ne sont jamais copiés
 * dans la table `publications` (propriété IRD). On stocke juste les métadonnées
 * pour les afficher dans le portail avec un lien vers la source d'origine.
 *
 * Les chercheurs UMMISCO souhaitant ajouter leurs propres articles
 * utilisent le workflow de soumission dédié (Publication + Document upload).
 */
class PublicationImportService
{
    public function __construct(
        private SemanticScholarService $semanticScholar,
        private OpenAlexService        $openAlex,
        private ArxivService           $arxiv,
        private UnpaywallService       $unpaywall,
    ) {}

    /**
     * Fetch toutes les sources pour une requête donnée
     * et persiste les résultats dans external_publications.
     *
     * @param  string  $query   Mots-clés de recherche
     * @param  string  $source  'all' | 'semantic_scholar' | 'openalex' | 'arxiv'
     * @param  int     $limit   Limite par source
     * @return array            Statistiques : ['fetched' => n, 'new' => n, 'updated' => n]
     */
    public function fetchAndStore(string $query, string $source = 'all', int $limit = 50): array
    {
        $stats = ['fetched' => 0, 'new' => 0, 'updated' => 0, 'errors' => []];

        $articles = [];

        try {
            if ($source === 'all' || $source === 'semantic_scholar') {
                $ss = $this->semanticScholar->searchByQuery($query, $limit);
                Log::info("[Import] SemanticScholar: {$query} → " . count($ss) . " résultats");
                $articles = array_merge($articles, $ss);
            }

            if ($source === 'all' || $source === 'openalex') {
                $oa = $this->openAlex->searchByQuery($query, $limit);
                Log::info("[Import] OpenAlex: {$query} → " . count($oa) . " résultats");
                $articles = array_merge($articles, $oa);
            }

            if ($source === 'all' || $source === 'arxiv') {
                $ax = $this->arxiv->search($query, $limit);
                Log::info("[Import] arXiv: {$query} → " . count($ax) . " résultats");
                $articles = array_merge($articles, $ax);
            }

        } catch (\Throwable $e) {
            $stats['errors'][] = $e->getMessage();
            Log::error("[Import] Erreur fetch", ['error' => $e->getMessage()]);
        }

        $stats['fetched'] = count($articles);

        // Enrichissement PDF via Unpaywall pour les articles avec DOI
        $articles = $this->unpaywall->enrichWithPdfUrls($articles);

        // Persistance avec déduplication
        foreach ($articles as $article) {
            try {
                $result = $this->upsert($article);
                if ($result === 'new') {
                    $stats['new']++;
                } elseif ($result === 'updated') {
                    $stats['updated']++;
                }
            } catch (\Throwable $e) {
                Log::warning("[Import] Erreur upsert", [
                    'source'      => $article['source'] ?? '?',
                    'external_id' => $article['external_id'] ?? '?',
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    /**
     * Insert ou met à jour un article externe.
     * Déduplication par (source, external_id).
     *
     * @return string 'new' | 'updated' | 'skipped'
     */
    private function upsert(array $article): string
    {
        if (empty($article['source']) || empty($article['external_id'])) {
            return 'skipped';
        }

        $existing = ExternalPublication::where('source', $article['source'])
                                       ->where('external_id', $article['external_id'])
                                       ->first();

        $payload = [
            'doi'             => $article['doi'] ?? null,
            'pdf_url'         => $article['pdf_url'] ?? null,
            'titre'           => mb_substr($article['titre'] ?? '', 0, 1000),
            'resume'          => $article['resume'] ?? null,
            'auteurs'         => $article['auteurs'] ?? null,
            'journal'         => mb_substr($article['journal'] ?? '', 0, 500),
            'annee'           => mb_substr($article['annee'] ?? '', 0, 4),
            'type_publication'=> $article['type_publication'] ?? 'article',
            'raw_data'        => $article['raw_data'] ?? $article,
            'fetched_at'      => now(),
            'statut'          => ExternalPublication::STATUT_DISPONIBLE,
        ];

        if ($existing) {
            // Met à jour uniquement si on a de nouvelles infos (ex: PDF url enrichi)
            $existing->update($payload);
            return 'updated';
        }

        ExternalPublication::create(array_merge($payload, [
            'source'      => $article['source'],
            'external_id' => $article['external_id'],
        ]));

        return 'new';
    }

    /**
     * Fetch par auteur (utile pour les crons ciblant les membres UMMISCO)
     */
    public function fetchByAuthor(string $authorName, int $limit = 50): array
    {
        $articles = [];

        $ss = $this->semanticScholar->searchByAuthor($authorName, $limit);
        $ax = $this->arxiv->searchByAuthor($authorName, $limit);

        $articles = array_merge($articles, $ss, $ax);
        $articles = $this->unpaywall->enrichWithPdfUrls($articles);

        $stats = ['fetched' => count($articles), 'new' => 0, 'updated' => 0];

        foreach ($articles as $article) {
            $result = $this->upsert($article);
            if ($result === 'new') $stats['new']++;
            elseif ($result === 'updated') $stats['updated']++;
        }

        return $stats;
    }
}
