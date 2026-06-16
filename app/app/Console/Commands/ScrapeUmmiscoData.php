<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Modules\UMMISCO\Models\Centre;
use Illuminate\Support\Facades\File;

class ScrapeUmmiscoData extends Command
{
    protected $signature = 'ummisco:scrape';
    protected $description = 'Scrape le site UMMISCO pour mettre à jour le logo, les centres et les membres';

    public function handle()
    {
        $this->info('Début du scraping de UMMISCO...');

        // 1. Scraping du Logo
        $this->info('1. Récupération du logo...');
        try {
            $response = Http::withOptions(['verify' => false])->get('https://ummisco.fr');
            $html = $response->body();
            
            // Cherche le logo (en général class "custom-logo" ou "logo")
            if (preg_match('/<img[^>]+src="([^"]+logo[^"]+)"/i', $html, $matches) || preg_match('/<img[^>]+src="([^"]+ummisco[^"]*\.png)"/i', $html, $matches)) {
                $logoUrl = $matches[1];
                $this->info("Logo trouvé : $logoUrl");
                
                // Télécharger l'image et convertir en base64
                $imageContent = Http::withOptions(['verify' => false])->get($logoUrl)->body();
                $mimeType = 'image/png';
                if (str_ends_with(strtolower($logoUrl), '.webp')) $mimeType = 'image/webp';
                if (str_ends_with(strtolower($logoUrl), '.svg')) $mimeType = 'image/svg+xml';
                
                $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                
                // Remplacer dans le fichier logos.js
                $logosJsPath = resource_path('js/utils/logos.js');
                if (File::exists($logosJsPath)) {
                    $jsContent = File::get($logosJsPath);
                    $jsContent = preg_replace(
                        '/export const logoUmmisco = \'[^\']+\';/',
                        'export const logoUmmisco = \'' . $base64 . '\';',
                        $jsContent
                    );
                    File::put($logosJsPath, $jsContent);
                    $this->info("Le fichier logos.js a été mis à jour avec le nouveau logo.");
                }
            } else {
                $this->warn("Impossible de trouver l'URL du logo sur la page d'accueil.");
            }
        } catch (\Exception $e) {
            $this->error('Erreur lors du scraping du logo: ' . $e->getMessage());
        }

        // 2. Scraping des Centres
        $this->info('2. Récupération des Centres...');
        Centre::truncate();
        try {
            $response = Http::withOptions(['verify' => false])->get('https://ummisco.fr/centres/');
            $html = $response->body();
            
            // Les centres sont listés par exemple <a href=".../centre-france/">Centre France</a>
            preg_match_all('/<a[^>]+href="([^"]+\/centre-[^"]+)"[^>]*>([^<]+)<\/a>/i', $html, $matches, PREG_SET_ORDER);
            
            $centresMap = [];
            foreach ($matches as $match) {
                $url = $match[1];
                $nom = trim(strip_tags($match[2]));
                if (str_contains(strtolower($nom), 'centre') && !isset($centresMap[$nom])) {
                    $centre = Centre::create(['nom' => $nom, 'url' => $url]);
                    $centresMap[$nom] = $centre->id;
                    $this->line("- Ajouté : $nom");
                }
            }
        } catch (\Exception $e) {
            $this->error('Erreur lors du scraping des centres: ' . $e->getMessage());
        }

        $this->info('Scraping UMMISCO terminé avec succès !');
    }
}
