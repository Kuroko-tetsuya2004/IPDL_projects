<?php

namespace App\Modules\Dataset\Controllers;

use App\Modules\Content\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Modules\Integration\Services\DatasetImportService;
use App\Modules\Integration\Models\ExternalDataset;
use App\Modules\User\Models\User;

class DatasetController extends Controller
{
    public function index(): Response
    {
        $userId = session('user_id');

        $datasets = Publication::where('type', 'dataset')
            ->where('auteur_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'titre_fr', 'statut', 'visibilite', 'created_at', 'date_publication']);

        return Inertia::render('Dataset/MesDatasets', [
            'datasets' => $datasets,
        ]);
    }

    // ── Importation DataCite ────────────────────────────────────────────────

    /**
     * Importe un dataset spécifiquement via son DOI DataCite
     */
    public function importByDoi(Request $request, DatasetImportService $importService)
    {
        $request->validate([
            'doi' => 'required|string|min:5|max:255',
        ]);

        $user = User::findOrFail(session('user_id'));
        
        try {
            $dataset = $importService->fetchAndImportByDoi($request->input('doi'), $user);

            if (!$dataset) {
                return back()->with('error', 'Dataset introuvable sur DataCite avec ce DOI.');
            }

            return back()->with('success', 'Dataset importé avec succès depuis DataCite.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("[Dataset Import DOI] Erreur : " . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'importation depuis DataCite.');
        }
    }

    /**
     * Synchronise les datasets du chercheur depuis son profil ORCID
     */
    public function syncOrcid(DatasetImportService $importService)
    {
        $user = User::findOrFail(session('user_id'));

        if (empty($user->orcid_id)) {
            return back()->with('error', 'Vous devez d\'abord renseigner votre ORCID dans votre profil.');
        }

        try {
            $stats = $importService->syncUserOrcid($user);

            if ($stats['fetched'] === 0) {
                return back()->with('info', 'Aucun dataset trouvé sur DataCite pour votre ORCID.');
            }

            return back()->with('success', "Synchronisation terminée : {$stats['fetched']} datasets trouvés, {$stats['new']} nouveaux importés.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("[Dataset ORCID Sync] Erreur : " . $e->getMessage());
            return back()->with('error', 'Une erreur inattendue est survenue lors de la synchronisation ORCID.');
        }
    }

    /**
     * Recherche en direct sur l'API DataCite
     */
    public function fetchLive(Request $request, DatasetImportService $importService)
    {
        $query = $request->input('q');
        if (!$query) {
            return back()->with('error', 'Veuillez saisir un terme de recherche.');
        }

        $stats = $importService->fetchAndStore($query, 50);

        return back()->with('success', "Recherche DataCite terminée : {$stats['fetched']} trouvés, {$stats['new']} nouveaux importés dans l'index.");
    }

    /**
     * Recherche et liste les datasets DataCite (fallback pour l'interface publique ou admin)
     */
    public function externalIndex(Request $request)
    {
        $query  = $request->get('q', '');
        $annee  = $request->get('annee', '');
        $perPage = 20;

        $datasets = ExternalDataset::disponible()
            ->when($query, fn($q) => $q->where(fn($sq) =>
                $sq->where('titre', 'ilike', "%{$query}%")
                   ->orWhere('resume', 'ilike', "%{$query}%")
                   ->orWhere('auteurs', 'ilike', "%{$query}%")
                   ->orWhere('doi', 'ilike', "%{$query}%")
            ))
            ->when($annee, fn($q) => $q->where('annee', $annee))
            ->orderByDesc('annee')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Dataset/ExternalDatasets', [
            'datasets' => $datasets,
            'filters'  => compact('query', 'annee'),
        ]);
    }
}
