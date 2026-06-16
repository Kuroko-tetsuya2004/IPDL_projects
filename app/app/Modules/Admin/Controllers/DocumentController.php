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
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        $validated = $request->validate([
            'type_document' => 'required|string|in:convention_stage,prestation_service',
            'donnees' => 'required|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $reference = 'DOC-' . date('Y') . '-' . strtoupper(Str::random(6));
        $filePath = null;

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = $reference . '.pdf';
            // Store in the 'minio' disk or default disk
            $filePath = $file->storeAs('documents_administratifs/' . date('Y/m'), $filename, env('FILESYSTEM_DISK', 'public'));
        }

        $document = DocumentAdministratif::create([
            'user_id' => session('user_id'),
            'type_document' => $validated['type_document'],
            'reference' => $reference,
            'donnees' => json_decode($validated['donnees'], true),
            'file_path' => $filePath,
        ]);

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

        $disk = env('FILESYSTEM_DISK', 'public');
        if (!Storage::disk($disk)->exists($document->file_path)) {
            return back()->with('error', 'Fichier introuvable sur le stockage.');
        }

        return Storage::disk($disk)->download($document->file_path, $document->reference . '.pdf');
    }
}
