<?php

namespace App\Modules\Dataset\Controllers;

use App\Modules\Content\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

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

    public function create(): Response
    {
        return Inertia::render('Dataset/Nouveau');
    }

    public function store(Request $request)
    {
        $userId = session('user_id');

        // Prétraitement de mots_cles s'il est envoyé sous forme de chaîne de caractères
        if ($request->has('mots_cles') && is_string($request->input('mots_cles'))) {
            $motsCles = array_filter(array_map('trim', explode(',', $request->input('mots_cles'))));
            $request->merge(['mots_cles' => $motsCles]);
        }

        $validated = $request->validate([
            'titre_fr'    => 'required|string|max:500',
            'titre_en'    => 'nullable|string|max:500',
            'resume_fr'   => 'required|string',
            'licence'     => 'required|string|max:100', // RG-007
            'mots_cles'   => 'nullable|array',
            'axe_id'      => 'nullable|uuid|exists:axes_thematiques,id',
            'fichier'     => 'nullable|file|max:51200',
        ]);

        $licenceMap = [
            'CC BY 4.0' => 'cc_by',
            'CC BY-SA 4.0' => 'cc_by_sa',
            'CC BY-NC 4.0' => 'cc_by_nc',
            'CC BY-NC-SA 4.0' => 'cc_by_nc_sa',
            'CC0 1.0 (Domaine public)' => 'cc0',
            'Propriétaire' => 'proprietary',
        ];
        $dbLicence = $licenceMap[$validated['licence']] ?? 'restricted';

        return DB::transaction(function () use ($validated, $userId, $dbLicence, $request) {
            $pub = Publication::create([
                'auteur_id'        => $userId,
                'type'             => 'dataset',
                'statut'           => 'published',
                'visibilite'       => 'public',
                'titre_fr'         => $validated['titre_fr'],
                'titre_en'         => $validated['titre_en'] ?? null,
                'resume_fr'        => $validated['resume_fr'],
                'mots_cles'        => $validated['mots_cles'] ?? null,
                'axe_id'           => $validated['axe_id'] ?? null,
                'date_publication' => now(),
            ]);

            DB::table('datasets')->insert([
                'publication_id'   => $pub->id,
                'licence'          => $dbLicence,
                'format_principal' => $request->hasFile('fichier') ? strtoupper($request->file('fichier')->getClientOriginalExtension()) : 'CSV',
                'version'          => '1.0',
            ]);

            if ($request->hasFile('fichier')) {
                $file = $request->file('fichier');
                $path = $file->store('datasets', 'minio');

                DB::table('datasets_fichiers')->insert([
                    'id'            => \Illuminate\Support\Str::uuid(),
                    'dataset_id'    => $pub->id,
                    'nom'           => $file->getClientOriginalName(),
                    'chemin_minio'  => $path,
                    'bucket_minio'  => 'ummisco-public',
                    'taille_octets' => $file->getSize(),
                    'format'        => strtoupper($file->getClientOriginalExtension()),
                    'est_principal' => true,
                    'created_at'    => now(),
                ]);
            }

            return redirect()->route('mes-datasets')
                ->with('success', 'Dataset « ' . $pub->titre_fr . ' » créé avec succès.');
        });
    }
}
