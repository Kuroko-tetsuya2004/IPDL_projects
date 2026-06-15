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
        private CrossrefService        $crossref,
        private UnpaywallService       $unpaywall,
    ) {}

    /**
     * Fetch toutes les sources pour une requête donnée
     * et persiste les résultats dans external_publications.
     *
     * @param  string  $query   Mots-clés de recherche
     * @param  string  $source  'all' | 'semantic_scholar' | 'openalex' | 'arxiv' | 'crossref'
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

            if ($source === 'all' || $source === 'crossref') {
                $cr = $this->crossref->searchByQuery($query, $limit);
                Log::info("[Import] CrossRef: {$query} → " . count($cr) . " résultats");
                $articles = array_merge($articles, $cr);
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
        $cr = $this->crossref->searchByAuthor($authorName, $limit);

        $articles = array_merge($articles, $ss, $ax, $cr);
        $articles = $this->unpaywall->enrichWithPdfUrls($articles);

        $stats = ['fetched' => count($articles), 'new' => 0, 'updated' => 0];

        foreach ($articles as $article) {
            $result = $this->upsert($article);
            if ($result === 'new') $stats['new']++;
            elseif ($result === 'updated') $stats['updated']++;
        }

        return $stats;
    }

    /**
     * Synchronise les publications d'un utilisateur via son ORCID
     * Import direct dans la table `publications`
     */
    public function syncUserOrcid(\App\Modules\User\Models\User $user): array
    {
        if (empty($user->orcid_id)) {
            return ['fetched' => 0, 'new' => 0];
        }

        $articles = $this->openAlex->searchByOrcid($user->orcid_id, 100);
        $articles = $this->unpaywall->enrichWithPdfUrls($articles);

        $stats = ['fetched' => count($articles), 'new' => 0];

        foreach ($articles as $article) {
            // Sauvegarder d'abord en tant que publication externe pour garder l'historique
            $this->upsert($article);

            // Vérifier si cette publication est déjà dans le profil du chercheur (via doi ou titre)
            $exists = \App\Modules\Content\Models\Publication::where('auteur_id', $user->id)
                ->where(function ($q) use ($article) {
                    $q->where('titre_fr', 'ilike', $article['titre'])
                      ->orWhere('resume_fr', 'like', '%' . ($article['doi'] ?? 'NO_DOI') . '%');
                })->exists();

            if (!$exists) {
                // Créer la publication interne
                $motsCles = null;
                if (isset($article['raw_data']['concepts'])) {
                    $motsCles = collect($article['raw_data']['concepts'])->pluck('display_name')->take(5)->toArray();
                }

                \DB::transaction(function () use ($user, $article, $motsCles) {
                    $publication = \App\Modules\Content\Models\Publication::create([
                        'titre_fr'           => mb_substr($article['titre'] ?? 'Sans titre', 0, 500),
                        'resume_fr'          => $article['resume'] ?? 'Résumé non disponible.',
                        'doi'                => $article['doi'] ?? null,
                        'pdf_url'            => $article['pdf_url'] ?? null,
                        'url_externe'        => $article['doi'] ? 'https://doi.org/' . $article['doi'] : null,
                        'auteurs_externes'   => isset($article['auteurs']) ? json_decode($article['auteurs'], true) : null,
                        'type'               => 'article', // OpenAlex renvoie souvent 'article'
                        'statut'             => $user->requiresWorkflow() ? \App\Modules\Content\Models\Publication::STATUS_SUBMITTED : \App\Modules\Content\Models\Publication::STATUS_PUBLISHED,
                        'visibilite'         => 'public',
                        'langue_principale'  => 'fr',
                        'auteur_id'          => $user->id,
                        'axe_id'             => $user->axe_principal_id, // Par défaut sur l'axe du chercheur
                        'mots_cles'          => $motsCles,
                        'date_soumission'    => now(),
                        'date_publication'   => $user->canPublishDirectly() ? ($article['annee'] ? $article['annee'].'-01-01' : now()) : null,
                        'commentaire_auteur' => 'Import automatique via ORCID (' . $user->orcid_id . ')',
                    ]);

                    if ($user->requiresWorkflow()) {
                        $delaiJours = (int) \DB::table('parametres_systeme')->where('cle', 'workflow_delai_jours')->value('valeur') ?: 14;
                        \App\Modules\Content\Models\WorkflowValidation::create([
                            'publication_id'     => $publication->id,
                            'soumetteur_id'      => $user->id,
                            'statut'             => \App\Modules\Content\Models\WorkflowValidation::STATUS_PENDING,
                            'version'            => 1,
                            'date_soumission'    => now(),
                            'date_limite'        => now()->addDays($delaiJours),
                        ]);
                    }
                });
                $stats['new']++;
            }
        }

        return $stats;
    }

    /**
     * Importe une publication spécifique via son DOI
     */
    public function fetchAndImportByDoi(string $doi, \App\Modules\User\Models\User $user): ?\App\Modules\Content\Models\Publication
    {
        $article = $this->openAlex->searchByDoi($doi);
        
        if (!$article) {
            return null;
        }

        // Unpaywall enrich
        $articles = $this->unpaywall->enrichWithPdfUrls([$article]);
        $article = $articles[0];

        // Sauvegarde historique
        $this->upsert($article);

        // Vérifier si elle existe déjà dans le portail
        $existingPub = \App\Modules\Content\Models\Publication::where('auteur_id', $user->id)
            ->where(function ($q) use ($article) {
                $q->where('titre_fr', 'ilike', $article['titre'])
                  ->orWhere('resume_fr', 'like', '%' . ($article['doi'] ?? 'NO_DOI') . '%');
            })->first();

        if ($existingPub) {
            return $existingPub; // Déjà importée
        }

        $motsCles = null;
        if (isset($article['raw_data']['concepts'])) {
            $motsCles = collect($article['raw_data']['concepts'])->pluck('display_name')->take(5)->toArray();
        }

        $publication = null;

        \DB::transaction(function () use ($user, $article, $motsCles, &$publication) {
            $publication = \App\Modules\Content\Models\Publication::create([
                'titre_fr'           => mb_substr($article['titre'] ?? 'Sans titre', 0, 500),
                'resume_fr'          => $article['resume'] ?? 'Résumé non disponible.',
                'doi'                => $article['doi'] ?? null,
                'pdf_url'            => $article['pdf_url'] ?? null,
                'url_externe'        => $article['doi'] ? 'https://doi.org/' . $article['doi'] : null,
                'auteurs_externes'   => isset($article['auteurs']) ? json_decode($article['auteurs'], true) : null,
                'type'               => 'article',
                'statut'             => $user->requiresWorkflow() ? \App\Modules\Content\Models\Publication::STATUS_SUBMITTED : \App\Modules\Content\Models\Publication::STATUS_PUBLISHED,
                'visibilite'         => 'public',
                'langue_principale'  => 'fr',
                'auteur_id'          => $user->id,
                'axe_id'             => $user->axe_principal_id,
                'mots_cles'          => $motsCles,
                'date_soumission'    => now(),
                'date_publication'   => $user->canPublishDirectly() ? ($article['annee'] ? $article['annee'].'-01-01' : now()) : null,
                'commentaire_auteur' => 'Import via DOI (' . ($article['doi'] ?? 'N/A') . ')',
            ]);

            if ($user->requiresWorkflow()) {
                $delaiJours = (int) \DB::table('parametres_systeme')->where('cle', 'workflow_delai_jours')->value('valeur') ?: 14;
                \App\Modules\Content\Models\WorkflowValidation::create([
                    'publication_id'     => $publication->id,
                    'soumetteur_id'      => $user->id,
                    'statut'             => \App\Modules\Content\Models\WorkflowValidation::STATUS_PENDING,
                    'version'            => 1,
                    'date_soumission'    => now(),
                    'date_limite'        => now()->addDays($delaiJours),
                ]);
            }
        });

        return $publication;
    }
}
