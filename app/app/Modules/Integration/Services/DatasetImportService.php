<?php

namespace App\Modules\Integration\Services;

use App\Modules\Integration\Models\ExternalDataset;
use App\Modules\Content\Models\Publication;
use App\Modules\Content\Models\WorkflowValidation;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DatasetImportService
 *
 * Orchestre la récupération de datasets externes via DataCiteService,
 * leur persistance dans `external_datasets` (lecture seule/mise en cache),
 * et leur importation réelle dans le profil d'un chercheur (tables `publications` + `datasets`).
 */
class DatasetImportService
{
    public function __construct(
        private DataCiteService $dataCite
    ) {}

    /**
     * Récupère un dataset par son DOI et l'importe directement pour l'utilisateur.
     *
     * @return Publication|null Le dataset importé (model Publication) ou null si introuvable
     */
    public function fetchAndImportByDoi(string $doi, User $user): ?Publication
    {
        $rawDataset = $this->dataCite->fetchByDoi($doi);
        
        if (!$rawDataset) {
            return null;
        }

        // Sauvegarde dans la table externe pour historique
        $this->upsert($rawDataset);

        // Vérifier si le dataset est déjà dans le portail pour ce chercheur
        $existing = Publication::where('auteur_id', $user->id)
            ->where('type', Publication::TYPE_DATASET)
            ->where(function ($q) use ($rawDataset) {
                $q->where('doi', $rawDataset['doi'])
                  ->orWhere('titre_fr', 'ilike', $rawDataset['titre']);
            })->first();

        if ($existing) {
            return $existing;
        }

        return $this->importToProfile($rawDataset, $user);
    }

    /**
     * Synchronise tous les datasets d'un chercheur via son ORCID.
     *
     * @return array Statistiques ['fetched' => int, 'new' => int]
     */
    public function syncUserOrcid(User $user): array
    {
        if (empty($user->orcid_id)) {
            return ['fetched' => 0, 'new' => 0];
        }

        $datasets = $this->dataCite->fetchByOrcid($user->orcid_id, 100);
        
        $stats = ['fetched' => count($datasets), 'new' => 0];

        foreach ($datasets as $rawDataset) {
            $this->upsert($rawDataset);

            $exists = Publication::where('auteur_id', $user->id)
                ->where('type', Publication::TYPE_DATASET)
                ->where(function ($q) use ($rawDataset) {
                    $q->where('doi', $rawDataset['doi'])
                      ->orWhere('titre_fr', 'ilike', $rawDataset['titre']);
                })->exists();

            if (!$exists) {
                $this->importToProfile($rawDataset, $user);
                $stats['new']++;
            }
        }

        return $stats;
    }

    /**
     * Recherche libre et stockage dans external_datasets (pour fallback de recherche public).
     */
    public function fetchAndStore(string $query, int $limit = 50): array
    {
        $stats = ['fetched' => 0, 'new' => 0, 'updated' => 0];
        
        $datasets = $this->dataCite->searchByQuery($query, $limit);
        $stats['fetched'] = count($datasets);

        foreach ($datasets as $dataset) {
            $result = $this->upsert($dataset);
            if ($result === 'new') $stats['new']++;
            elseif ($result === 'updated') $stats['updated']++;
        }

        return $stats;
    }

    /**
     * Insert ou met à jour un dataset dans la table `external_datasets`.
     */
    public function upsert(array $dataset): string
    {
        if (empty($dataset['doi'])) {
            return 'skipped'; // On force le DOI comme identifiant unique
        }

        $existing = ExternalDataset::where('doi', $dataset['doi'])->first();

        $payload = [
            'source'        => $dataset['source'],
            'external_id'   => $dataset['external_id'],
            'titre'         => mb_substr($dataset['titre'] ?? '', 0, 1000),
            'resume'        => $dataset['resume'] ?? null,
            'auteurs'       => $dataset['auteurs'] ?? null,
            'annee'         => mb_substr($dataset['annee'] ?? '', 0, 4),
            'type_dataset'  => mb_substr($dataset['type_dataset'] ?? 'dataset', 0, 50),
            'licence'       => mb_substr($dataset['licence'] ?? '', 0, 100),
            'taille_octets' => $dataset['taille_octets'] ?? null,
            'formats'       => $dataset['formats'] ?? null,
            'lien_acces'    => mb_substr($dataset['lien_acces'] ?? '', 0, 1000),
            'editeur'       => mb_substr($dataset['editeur'] ?? '', 0, 500),
            'version'       => mb_substr($dataset['version'] ?? '', 0, 50),
            'raw_data'      => $dataset['raw_data'] ?? $dataset,
            'fetched_at'    => now(),
            'statut'        => ExternalDataset::STATUT_DISPONIBLE,
        ];

        if ($existing) {
            $existing->update($payload);
            return 'updated';
        }

        ExternalDataset::create(array_merge($payload, ['doi' => $dataset['doi']]));
        return 'new';
    }

    /**
     * Importe effectivement le dataset normalisé dans les tables `publications` et `datasets`.
     */
    private function importToProfile(array $dataset, User $user): Publication
    {
        return DB::transaction(function () use ($dataset, $user) {
            // 1. Création de la publication mère
            $publication = Publication::create([
                'titre_fr'           => mb_substr($dataset['titre'] ?? 'Sans titre', 0, 500),
                'resume_fr'          => $dataset['resume'] ?? 'Résumé non disponible.',
                'doi'                => $dataset['doi'] ?? null,
                'url_externe'        => $dataset['lien_acces'] ?? ($dataset['doi'] ? 'https://doi.org/' . $dataset['doi'] : null),
                'auteurs_externes'   => isset($dataset['auteurs']) ? json_decode($dataset['auteurs'], true) : null,
                'type'               => Publication::TYPE_DATASET,
                // Selon DatasetController::store actuel, les datasets sont souvent créés "published"
                // On respecte la logique du workflow global :
                'statut'             => $user->requiresWorkflow() ? Publication::STATUS_SUBMITTED : Publication::STATUS_PUBLISHED,
                'visibilite'         => 'public',
                'langue_principale'  => 'fr',
                'auteur_id'          => $user->id,
                'axe_id'             => $user->axe_principal_id,
                'date_soumission'    => now(),
                'date_publication'   => $user->canPublishDirectly() ? ($dataset['annee'] ? $dataset['annee'].'-01-01' : now()) : null,
                'commentaire_auteur' => 'Import automatique via DataCite (' . ($dataset['doi'] ?? 'N/A') . ')',
            ]);

            // Mapping de la licence
            $licenceMap = [
                'cc by 4.0'       => 'cc_by',
                'cc-by-4.0'       => 'cc_by',
                'cc by-sa 4.0'    => 'cc_by_sa',
                'cc-by-sa-4.0'    => 'cc_by_sa',
                'cc by-nc 4.0'    => 'cc_by_nc',
                'cc-by-nc-4.0'    => 'cc_by_nc',
                'cc by-nc-sa 4.0' => 'cc_by_nc_sa',
                'cc-by-nc-sa-4.0' => 'cc_by_nc_sa',
                'cc0 1.0'         => 'cc0',
                'cc0-1.0'         => 'cc0',
            ];
            $rawLicence = strtolower($dataset['licence'] ?? '');
            $dbLicence = 'restricted'; // Défaut
            foreach ($licenceMap as $k => $v) {
                if (str_contains($rawLicence, $k)) {
                    $dbLicence = $v;
                    break;
                }
            }

            // 2. Création de l'entrée enfant dans `datasets`
            DB::table('datasets')->insert([
                'publication_id'      => $publication->id,
                'licence'             => $dbLicence,
                'format_principal'    => isset($dataset['formats'][0]) ? strtoupper(explode('/', $dataset['formats'][0])[1] ?? $dataset['formats'][0]) : 'UNKNOWN',
                'formats_disponibles' => !empty($dataset['formats']) ? '{' . implode(',', array_map(fn($f) => '"' . str_replace('"', '\"', $f) . '"', $dataset['formats'])) . '}' : null, // Pour tableau PostgreSQL TEXT[]
                'taille_totale_mo'    => isset($dataset['taille_octets']) ? round($dataset['taille_octets'] / 1048576, 2) : null,
                'version'             => $dataset['version'] ?? '1.0',
                'doi'                 => $dataset['doi'] ?? null,
                'lien_externe'        => $dataset['lien_acces'] ?? null,
            ]);

            // 3. Workflow si nécessaire
            if ($user->requiresWorkflow()) {
                $delaiJours = (int) DB::table('parametres_systeme')->where('cle', 'workflow_delai_jours')->value('valeur') ?: 14;
                WorkflowValidation::create([
                    'publication_id'  => $publication->id,
                    'soumetteur_id'   => $user->id,
                    'statut'          => WorkflowValidation::STATUS_PENDING,
                    'version'         => 1,
                    'date_soumission' => now(),
                    'date_limite'     => now()->addDays($delaiJours),
                ]);
            }

            return $publication;
        });
    }
}
