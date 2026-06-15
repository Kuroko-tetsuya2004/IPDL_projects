<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Content\Models\Publication;
use Illuminate\Support\Str;

class ImportPublicationsJsonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publications:import-json {file=pubs_to_import.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importe une liste de publications depuis un fichier JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $path = storage_path('app/' . $file);

        if (!file_exists($path)) {
            $this->error("Le fichier $path n'existe pas.");
            return Command::FAILURE;
        }

        $json = file_get_contents($path);
        $publications = json_decode($json, true);

        if (!$publications) {
            $this->error("Erreur de parsing JSON ou fichier vide.");
            return Command::FAILURE;
        }

        $count = 0;
        $auteurId = \Illuminate\Support\Facades\DB::table('users')->value('id');

        if (!$auteurId) {
            $this->error("Aucun utilisateur trouvé en base de données pour attacher les publications.");
            return Command::FAILURE;
        }

        foreach ($publications as $pub) {
            $doi = null;

            if (isset($pub['DOI'])) {
                $doi = $pub['DOI'];
            } elseif (isset($pub['URL']) && strpos($pub['URL'], 'doi.org') !== false) {
                $doi = str_replace(['http://doi.org/', 'https://doi.org/'], '', $pub['URL']);
            }

            if ($doi) {
                \App\Jobs\ImportDoiJob::dispatch($doi, (string) $auteurId);
                $count++;
            }
        }

        $this->info("Importation programmée en arrière-plan. $count tâches envoyées à la queue pour traiter les DOI avec l'API OpenAlex.");
        return Command::SUCCESS;
    }
}
