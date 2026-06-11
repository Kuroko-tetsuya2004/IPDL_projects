<?php

namespace App\Console\Commands;

use App\Modules\Integration\Services\PublicationImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Commande Artisan pour importer les publications depuis les sources externes.
 *
 * Usages :
 *   php artisan publications:import
 *   php artisan publications:import --query="epidemiology agent-based Senegal" --source=openalex
 *   php artisan publications:import --author="Papa Serigne Mbaye"
 */
class ImportPublications extends Command
{
    protected $signature = 'publications:import
                            {--query=* : Requêtes de recherche (peuvent être multiples)}
                            {--author= : Nom d\'un auteur à cibler}
                            {--source=all : Source (all|semantic_scholar|openalex|arxiv)}
                            {--limit=50 : Nombre max de résultats par source}';

    protected $description = 'Importe les publications depuis Semantic Scholar, OpenAlex et arXiv';

    // Requêtes par défaut ciblant les thématiques UMMISCO
    private const DEFAULT_QUERIES = [
        'UMMISCO Dakar IRD',
        'agent-based model epidemiology Senegal',
        'malaria mathematical model West Africa',
        'complex systems simulation Africa',
        'biodiversity modelling Senegal IRD',
        'netlogo GAMA agent simulation',
    ];

    public function __construct(private PublicationImportService $importer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔬 Import des publications scientifiques — UMMISCO Portail');
        $this->newLine();

        $source = $this->option('source');
        $limit  = (int) $this->option('limit');
        $author = $this->option('author');
        $queries = $this->option('query');

        // Mode auteur
        if ($author) {
            $this->info("👤 Recherche par auteur : {$author}");
            $stats = $this->importer->fetchByAuthor($author, $limit);
            $this->displayStats($author, $stats);
            return self::SUCCESS;
        }

        // Mode requêtes
        if (empty($queries)) {
            $queries = self::DEFAULT_QUERIES;
            $this->comment('ℹ️  Aucune requête spécifiée — utilisation des requêtes UMMISCO par défaut');
        }

        $totalStats = ['fetched' => 0, 'new' => 0, 'updated' => 0];

        foreach ($queries as $query) {
            $this->info("🔍 Requête : \"{$query}\" [source: {$source}, limit: {$limit}]");

            $stats = $this->importer->fetchAndStore($query, $source, $limit);
            $this->displayStats($query, $stats);

            $totalStats['fetched']  += $stats['fetched'];
            $totalStats['new']      += $stats['new'];
            $totalStats['updated']  += $stats['updated'];

            // Pause entre les requêtes pour respecter les rate limits
            if (count($queries) > 1) {
                sleep(2);
            }
        }

        $this->newLine();
        $this->info('✅ Import terminé !');
        $this->table(
            ['Total fetched', 'Nouveaux', 'Mis à jour'],
            [[$totalStats['fetched'], $totalStats['new'], $totalStats['updated']]]
        );

        Log::info('[publications:import] Terminé', $totalStats);

        return self::SUCCESS;
    }

    private function displayStats(string $label, array $stats): void
    {
        $this->line(sprintf(
            '   ✓ %d récupérés — %d nouveaux — %d mis à jour',
            $stats['fetched'],
            $stats['new'],
            $stats['updated'],
        ));
        if (!empty($stats['errors'])) {
            foreach ($stats['errors'] as $error) {
                $this->warn("   ⚠ {$error}");
            }
        }
    }
}
