<?php

namespace App\Modules\Integration\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * DataCiteService
 *
 * Wrapper pour l'API publique DataCite REST v2 (https://api.datacite.org).
 * Aucune clé API requise pour la lecture. Limite : 250 req/sec.
 *
 * Supporte :
 *  - Recherche par DOI         : https://api.datacite.org/dois/{doi}
 *  - Recherche par ORCID       : https://api.datacite.org/dois?query=creators.nameIdentifiers.nameIdentifier:orcid.org/{orcid}
 *  - Recherche par mots-clés   : https://api.datacite.org/dois?query=...
 *
 * Format de sortie normalisé (tableau associatif) identique à celui
 * utilisé par OpenAlexService / SemanticScholarService pour la compatibilité
 * avec DatasetImportService::upsert().
 */
class DataCiteService
{
    private const BASE_URL    = 'https://api.datacite.org';
    private const TIMEOUT     = 20;
    private const USER_AGENT  = 'UMMISCO-Portail/1.0 (contact@ummisco.sn)';

    // ── Méthodes publiques ────────────────────────────────────────────────────

    /**
     * Récupère un dataset spécifique par son DOI.
     *
     * @param  string $doi  DOI complet ou court (ex: 10.5281/zenodo.1234)
     * @return array|null   Données normalisées ou null si introuvable
     */
    public function fetchByDoi(string $doi): ?array
    {
        // Nettoyer le DOI (retirer https://doi.org/ si présent)
        $doi = preg_replace('#^https?://(dx\.)?doi\.org/#i', '', trim($doi));

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withUserAgent(self::USER_AGENT)
                ->withHeaders(['Accept' => 'application/vnd.api+json'])
                ->get(self::BASE_URL . '/dois/' . rawurlencode($doi));

            if ($response->status() === 404) {
                Log::info("[DataCite] DOI introuvable : {$doi}");
                return null;
            }

            if (!$response->successful()) {
                Log::warning('[DataCite] Erreur API fetchByDoi', [
                    'doi'    => $doi,
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                ]);
                return null;
            }

            $data = $response->json('data');
            if (!$data) {
                return null;
            }

            return $this->normalize($data);

        } catch (ConnectionException $e) {
            Log::error('[DataCite] Connexion impossible (fetchByDoi)', [
                'doi'   => $doi,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Récupère tous les datasets liés à un ORCID.
     *
     * DataCite indexe les créateurs avec leurs identifiants ORCID.
     * L'ORCID peut être fourni au format court (0000-0002-1234-5678)
     * ou complet (https://orcid.org/0000-0002-1234-5678).
     *
     * @param  string $orcid  ORCID du chercheur
     * @param  int    $limit  Nombre max de résultats (défaut 50)
     * @return array          Tableau de datasets normalisés
     */
    public function fetchByOrcid(string $orcid, int $limit = 50): array
    {
        // Normaliser l'ORCID au format court pour la requête
        $orcidShort = preg_replace('#^https?://orcid\.org/#', '', $orcid);

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withUserAgent(self::USER_AGENT)
                ->withHeaders(['Accept' => 'application/vnd.api+json'])
                ->get(self::BASE_URL . '/dois', [
                    'query'         => "creators.nameIdentifiers.nameIdentifier:\"https://orcid.org/{$orcidShort}\" OR creators.nameIdentifiers.nameIdentifier:\"{$orcidShort}\" OR {$orcidShort}",
                    'resource-type-id' => 'dataset',
                    'page[size]'    => min($limit, 1000),
                    'sort'          => 'created',
                    'direction'     => 'desc',
                ]);

            if (!$response->successful()) {
                Log::warning('[DataCite] Erreur API fetchByOrcid', [
                    'orcid'  => $orcidShort,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $items = $response->json('data', []);

            Log::info("[DataCite] ORCID {$orcidShort} → " . count($items) . ' datasets');

            return $this->normalizeMany($items);

        } catch (ConnectionException $e) {
            Log::error('[DataCite] Connexion impossible (fetchByOrcid)', [
                'orcid' => $orcidShort,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Recherche libre par mots-clés.
     *
     * @param  string $query  Termes de recherche
     * @param  int    $limit  Nombre max de résultats
     * @return array          Datasets normalisés
     */
    public function searchByQuery(string $query, int $limit = 50): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withUserAgent(self::USER_AGENT)
                ->withHeaders(['Accept' => 'application/vnd.api+json'])
                ->get(self::BASE_URL . '/dois', [
                    'query'            => $query,
                    'resource-type-id' => 'dataset',
                    'page[size]'       => min($limit, 1000),
                    'sort'             => 'created',
                    'direction'        => 'desc',
                ]);

            if (!$response->successful()) {
                Log::warning('[DataCite] Erreur searchByQuery', [
                    'query'  => $query,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $items = $response->json('data', []);

            Log::info("[DataCite] Recherche \"{$query}\" → " . count($items) . ' datasets');

            return $this->normalizeMany($items);

        } catch (ConnectionException $e) {
            Log::error('[DataCite] Connexion impossible (searchByQuery)', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ── Normalisation ─────────────────────────────────────────────────────────

    /**
     * Normalise un tableau de résultats DataCite.
     */
    private function normalizeMany(array $items): array
    {
        return array_values(array_filter(
            array_map(fn($item) => $this->normalize($item), $items)
        ));
    }

    /**
     * Normalise un objet DataCite (format JSON:API) en format commun interne.
     *
     * Format DataCite JSON:API :
     *  - data.id          : DOI court (ex: 10.5281/zenodo.1234)
     *  - data.attributes  : métadonnées
     *    - titles[]       : [{title: "..."}]
     *    - creators[]     : [{name: "..."}]
     *    - descriptions[] : [{description: "..."}]
     *    - publicationYear: int
     *    - types          : {resourceType, resourceTypeGeneral}
     *    - rightsList[]   : [{rights: "CC BY 4.0", rightsUri: "..."}]
     *    - sizes[]        : ["1234 bytes"]
     *    - formats[]      : ["text/csv"]
     *    - version        : "1.0"
     *    - publisher      : "Zenodo"
     *    - url            : URL d'accès
     *
     * @return array|null  Format normalisé ou null si données insuffisantes
     */
    public function normalize(array $item): ?array
    {
        $doi   = $item['id'] ?? $item['attributes']['doi'] ?? null;
        $attrs = $item['attributes'] ?? [];

        if (!$doi) {
            return null;
        }

        // Titre principal
        $titre = null;
        foreach ($attrs['titles'] ?? [] as $t) {
            if (!isset($t['titleType'])) {
                $titre = $t['title'] ?? null;
                break;
            }
        }
        if (!$titre) {
            $titre = $attrs['titles'][0]['title'] ?? null;
        }

        // Résumé
        $resume = null;
        foreach ($attrs['descriptions'] ?? [] as $d) {
            if (in_array($d['descriptionType'] ?? '', ['Abstract', 'Methods', 'TechnicalInfo', ''])) {
                $resume = strip_tags($d['description'] ?? '');
                if ($resume) break;
            }
        }

        // Auteurs
        $auteurs = array_map(
            fn($c) => $c['name'] ?? ($c['givenName'] ?? '') . ' ' . ($c['familyName'] ?? ''),
            $attrs['creators'] ?? []
        );
        $auteurs = array_values(array_filter($auteurs));

        // Licence
        $licence = null;
        foreach ($attrs['rightsList'] ?? [] as $r) {
            $licence = $r['rights'] ?? null;
            if ($licence) break;
        }

        // Taille en octets
        $tailleOctets = null;
        foreach ($attrs['sizes'] ?? [] as $s) {
            if (preg_match('/(\d+)\s*(bytes?|octets?)/i', $s, $m)) {
                $tailleOctets = (int) $m[1];
                break;
            }
        }

        // Formats
        $formats = $attrs['formats'] ?? [];

        // Type de ressource
        $typeDataset = strtolower(
            $attrs['types']['resourceTypeGeneral'] ?? 'Dataset'
        );

        // Source (déduire depuis le DOI)
        $source = $this->inferSource($doi, $attrs['publisher'] ?? '');

        return [
            'source'          => $source,
            'external_id'     => $doi,
            'doi'             => $doi,
            'titre'           => $titre,
            'resume'          => $resume,
            'auteurs'         => json_encode($auteurs),
            'annee'           => (string) ($attrs['publicationYear'] ?? ''),
            'type_dataset'    => $typeDataset,
            'licence'         => $licence,
            'taille_octets'   => $tailleOctets,
            'formats'         => !empty($formats) ? $formats : null,
            'lien_acces'      => $attrs['url'] ?? ('https://doi.org/' . $doi),
            'editeur'         => $attrs['publisher'] ?? null,
            'version'         => $attrs['version'] ?? null,
            'raw_data'        => $item,
        ];
    }

    /**
     * Déduit la source (plateforme) depuis le DOI ou le publisher.
     */
    private function inferSource(string $doi, string $publisher): string
    {
        $doiLower       = strtolower($doi);
        $publisherLower = strtolower($publisher);

        if (str_contains($doiLower, '10.5281') || str_contains($publisherLower, 'zenodo')) {
            return 'zenodo';
        }
        if (str_contains($doiLower, '10.5061') || str_contains($publisherLower, 'dryad')) {
            return 'dryad';
        }
        if (str_contains($doiLower, '10.6084') || str_contains($publisherLower, 'figshare')) {
            return 'figshare';
        }
        if (str_contains($doiLower, '10.1594') || str_contains($publisherLower, 'pangaea')) {
            return 'pangaea';
        }
        return 'datacite';
    }
}
