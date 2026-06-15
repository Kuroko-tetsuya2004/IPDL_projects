<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Integration\Services\UmmiscoScraperService;

class ScrapeUmmiscoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ummisco:scrape {--type=all : Type de données à scraper (membres, centres, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape les données depuis le site officiel de ummisco.fr';

    /**
     * Execute the console command.
     */
    public function handle(UmmiscoScraperService $scraper)
    {
        $type = $this->option('type');

        $this->info("Début du scraping UMMISCO (type: {$type})...");

        if ($type === 'membres' || $type === 'all') {
            $this->info("Scraping des membres en cours...");
            $scraper->scrapeMembres();
            $this->info("✔ Membres traités.");
        }

        if ($type === 'centres' || $type === 'all') {
            $this->info("Scraping des centres en cours...");
            $scraper->scrapeCentres();
            $this->info("✔ Centres traités.");
        }

        if ($type === 'projets' || $type === 'all') {
            $this->info("Scraping des projets en cours...");
            $scraper->scrapeProjets();
            $this->info("✔ Projets traités.");
        }

        $this->info("Opération de scraping terminée !");
        return Command::SUCCESS;
    }
}
