<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\User\Models\User;
use App\Modules\Integration\Services\PublicationImportService;

class SyncOrcidPublications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orcid:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les publications depuis OpenAlex pour tous les utilisateurs ayant un ORCID renseigné';

    /**
     * Execute the console command.
     */
    public function handle(PublicationImportService $importService)
    {
        $this->info('Début de la synchronisation ORCID...');
        
        $users = User::whereNotNull('orcid_id')->where('orcid_id', '!=', '')->get();
        
        $totalFetched = 0;
        $totalNew = 0;

        foreach ($users as $user) {
            $this->info("Synchronisation pour {$user->prenom} {$user->nom} ({$user->orcid_id})...");
            try {
                $stats = $importService->syncUserOrcid($user);
                $this->info(" -> Résultat : {$stats['fetched']} trouvées, {$stats['new']} nouvelles.");
                
                $totalFetched += $stats['fetched'];
                $totalNew += $stats['new'];
            } catch (\Exception $e) {
                $this->error("Erreur pour l'utilisateur {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Terminé. Total trouvé: {$totalFetched}, Total nouveau: {$totalNew}.");
        return Command::SUCCESS;
    }
}
