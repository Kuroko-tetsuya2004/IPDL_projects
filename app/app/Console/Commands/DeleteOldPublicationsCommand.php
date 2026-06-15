<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Content\Models\Publication;
use Carbon\Carbon;

class DeleteOldPublicationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publications:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime les publications ajoutées la semaine dernière';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();

        $this->info("Recherche des publications créées entre le {$startOfLastWeek->toDateString()} et le {$endOfLastWeek->toDateString()}...");

        $count = Publication::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])->count();

        if ($count === 0) {
            $this->info("Aucune publication trouvée pour la semaine dernière.");
            return Command::SUCCESS;
        }

        if ($this->confirm("Voulez-vous vraiment supprimer ces {$count} publications ?")) {
            Publication::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])->delete();
            $this->info("{$count} publications supprimées avec succès.");
        } else {
            $this->info("Opération annulée.");
        }

        return Command::SUCCESS;
    }
}
