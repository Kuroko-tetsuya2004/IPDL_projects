<?php

namespace App\Jobs;

use App\Modules\Integration\Services\PublicationImportService;
use App\Modules\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportDoiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $doi;
    public $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $doi, string $userId)
    {
        $this->doi = $doi;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(PublicationImportService $importService): void
    {
        $user = User::find($this->userId);
        
        if (!$user) {
            Log::warning("ImportDoiJob : Utilisateur non trouvé ({$this->userId}) pour le DOI {$this->doi}");
            return;
        }

        try {
            $importService->fetchAndImportByDoi($this->doi, $user);
            Log::info("ImportDoiJob : DOI {$this->doi} importé avec succès.");
        } catch (\Exception $e) {
            Log::error("ImportDoiJob : Erreur lors de l'import du DOI {$this->doi}", ['error' => $e->getMessage()]);
        }
    }
}
