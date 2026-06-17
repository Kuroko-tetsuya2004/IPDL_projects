<?php

namespace App\Modules\Admin\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use App\Modules\Admin\Models\DocumentAdministratif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * DocumentController — Gestion des documents administratifs IRD
 * Convention de Stage, Reçu de Prestation de Service, Bon d'Achat
 */
class DocumentController extends Controller
{
    /**
     * Page index listant les 3 types de documents disponibles
     */
    public function index(): Response
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        return Inertia::render('Admin/Documents/Index');
    }

    /**
     * Formulaire Convention de Stage
     */
    public function conventionStage(): Response
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        return Inertia::render('Admin/Documents/ConventionStage');
    }

    /**
     * Formulaire Reçu de Prestation de Service
     */
    public function prestationService(): Response
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        return Inertia::render('Admin/Documents/PrestationService');
    }

    /**
     * Formulaire Bon d'Achat / Demande d'Achat
     */
    public function bonAchat(): Response
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        return Inertia::render('Admin/Documents/BonAchat');
    }

    /**
     * Historique des documents générés
     */
    public function historique(): Response
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        $documents = DocumentAdministratif::with('user:id,prenom,nom,email')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Documents/Historique', [
            'documents' => $documents
        ]);
    }

    /**
     * Sauvegarde du document dans MinIO (ou en BDD)
     */
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Début store document administratif', $request->all());

        if (session('user_role') !== 'super_admin') {
            \Illuminate\Support\Facades\Log::warning('Accès refusé. user_role = ' . session('user_role'));
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        try {
            $validated = $request->validate([
                'type_document' => 'required|string|in:convention_stage,prestation_service,bon_achat',
                'donnees' => 'required|string',
                'pdf_file' => 'nullable|file|max:10240', // Enlevé temporairement mimes:pdf pour tester
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Erreur de validation document administratif', $e->errors());
            throw $e;
        }

        $reference = 'DOC-' . date('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        $filePath = null;

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = $reference . '.pdf';
            $filePath = 'documents_administratifs/' . date('Y/m') . '/' . $filename;
            
            // Contournement du bug "SignatureDoesNotMatch" persistant avec Flysystem + MinIO
            // en utilisant directement le client S3 natif de l'adaptateur
            $contents = file_get_contents($file->getRealPath());
            $client = \Illuminate\Support\Facades\Storage::disk('minio')->getClient();
            $client->putObject([
                'Bucket' => config('filesystems.disks.minio.bucket'),
                'Key' => $filePath,
                'Body' => $contents,
                'ContentType' => 'application/pdf',
                'ACL' => 'public-read' // Assure la lecture publique si le bucket est configuré pour
            ]);
            
            \Illuminate\Support\Facades\Log::info("Résultat putObject S3Client MinIO : " . $filePath);
        }

        try {
            $document = DocumentAdministratif::create([
                'user_id' => session('user_id'),
                'type_document' => $validated['type_document'],
                'reference' => $reference,
                'donnees' => json_decode($validated['donnees'], true),
                'file_path' => $filePath,
            ]);
            \Illuminate\Support\Facades\Log::info("Document créé en BDD avec ID: " . $document->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erreur insertion BDD: " . $e->getMessage());
            throw $e;
        }

        return response()->json([
            'success' => true,
            'reference' => $document->reference,
            'message' => 'Document archivé avec succès.',
        ]);
    }

    /**
     * Téléchargement du document depuis l'historique
     */
    public function download($id)
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        $document = DocumentAdministratif::findOrFail($id);

        if (!$document->file_path) {
            return back()->with('error', 'Le fichier PDF n\'a pas été généré ou est introuvable.');
        }

        $disk = 'minio';
        if (!Storage::disk($disk)->exists($document->file_path)) {
            return back()->with('error', 'Fichier introuvable sur le stockage MinIO.');
        }

        return Storage::disk($disk)->download($document->file_path, $document->reference . '.pdf');
    }
}
