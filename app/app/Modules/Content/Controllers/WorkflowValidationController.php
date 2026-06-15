<?php

namespace App\Modules\Content\Controllers;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\Content\Models\Publication;
use App\Modules\Content\Models\WorkflowValidation;
use App\Modules\User\Models\AxeThematique;
use App\Modules\User\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * WorkflowValidationController — Gestion du workflow de validation
 *
 * Implémente le cycle de vie des soumissions pour les doctorants :
 *   - submit()  → soumission d'un contenu (RG-009)
 *   - approve() → validation par l'admin de l'axe (RG-011)
 *   - reject()  → rejet avec motif obligatoire (RG-014)
 *
 * Règles métier :
 *   RG-009 : Un doctorant ne peut publier directement.
 *   RG-011 : Le validateur est l'admin de l'axe thématique concerné.
 *   RG-012 : Un chercheur peut publier directement.
 *   RG-014 : Tout rejet doit être accompagné d'un motif écrit.
 */
class WorkflowValidationController extends Controller
{
    // ── Formulaire de soumission ─────────────────────────────────────────────

    /**
     * Affiche le formulaire de soumission d'une publication (GET).
     */
    public function create(): Response
    {
        $axes = AxeThematique::actif()->get(['id', 'nom_fr', 'code']);
        $userRole = session('user_role');

        return Inertia::render('Content/Submit', [
            'axes'     => $axes,
            'userRole' => $userRole,
        ]);
    }

    // ── Mes publications (utilisateur connecté) ───────────────────────────────

    /**
     * Liste les publications de l'utilisateur connecté.
     */
    public function mesPublications(): Response
    {
        $userId = session('user_id');

        $publications = Publication::where('auteur_id', $userId)
            ->with('axe:id,nom_fr,code')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Content/MesPublications', [
            'publications' => $publications,
            'userRole'     => session('user_role'),
        ]);
    }

    /**
     * Synchronise manuellement les publications via ORCID pour l'utilisateur connecté.
     */
    public function syncOrcid(\App\Modules\Integration\Services\PublicationImportService $importService): RedirectResponse
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);

        if (empty($user->orcid_id)) {
            return back()->withErrors(['orcid' => 'Veuillez renseigner votre ORCID dans votre profil avant de lancer la synchronisation.']);
        }

        try {
            $stats = $importService->syncUserOrcid($user);
            return back()->with('success', "Synchronisation ORCID terminée : {$stats['new']} nouvelle(s) publication(s) ajoutée(s).");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("[ORCID Sync] Erreur pour l'utilisateur {$userId}", ['error' => $e->getMessage()]);
            return back()->withErrors(['orcid' => 'Une erreur est survenue lors de la synchronisation avec ORCID.']);
        }
    }

    // ── Soumissions en attente (Admin) ────────────────────────────────────────

    /**
     * Liste les soumissions en attente de validation.
     */
    public function pending(): Response
    {
        $userId   = session('user_id');
        $userRole = session('user_role');
        $axeId    = null;

        if ($userRole === 'axe_admin') {
            $axe   = AxeThematique::where('responsable_id', $userId)->first();
            if (!$axe) {
                $user = User::find($userId);
                if ($user && $user->axe_principal_id) {
                    $axe = AxeThematique::find($user->axe_principal_id);
                }
            }
            $axeId = $axe?->id;
        }

        $query = WorkflowValidation::with([
            'publication:id,titre_fr,type,axe_id',
            'publication.axe:id,nom_fr,code',
            'soumetteur:id,nom,prenom',
        ]);

        // Doctorant : voir seulement ses propres soumissions
        if ($userRole === 'doctoral_student') {
            $query->where('soumetteur_id', $userId);
        } else {
            $query->pending();
            if ($axeId) {
                $query->byAxe($axeId);
            }
        }

        $soumissions = $query->orderBy('date_soumission', 'desc')->paginate(20);

        return Inertia::render('Content/Soumissions', [
            'soumissions' => $soumissions,
            'userRole'    => $userRole,
        ]);
    }

    // ── Soumission d'un contenu (Doctorant) ─────────────────────────────────

    /**
     * Soumet une publication à validation (Import Express par DOI).
     */
    public function submit(Request $request, \App\Modules\Integration\Services\PublicationImportService $importService): RedirectResponse
    {
        $validated = $request->validate([
            'doi' => 'required|string|max:255',
        ]);

        $userId = session('user_id');
        $user = User::findOrFail($userId);

        try {
            $publication = $importService->fetchAndImportByDoi($validated['doi'], $user);

            if (!$publication) {
                return back()->withErrors(['doi' => 'Impossible de trouver cette publication (DOI introuvable ou erreur de service).']);
            }

            if ($user->requiresWorkflow()) {

                // Log d'audit
                AuditLog::log(
                    AuditLog::ACTION_SUBMIT,
                    $user->id,
                    'publication',
                    $publication->id,
                    ['titre' => $publication->titre_fr, 'axe_id' => $publication->axe_id],
                );

                return redirect()->route('mes-publications')
                    ->with('success', 'Votre contenu a été soumis avec succès et est en attente de validation.');
            }

            // Chercheur : publication directe (RG-012)
            AuditLog::log(
                AuditLog::ACTION_PUBLISH,
                $user->id,
                'publication',
                $publication->id,
                ['titre' => $publication->titre_fr, 'mode' => 'direct'],
            );

            return redirect()->route('mes-publications')
                ->with('success', 'Votre contenu a été publié avec succès.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[DOI Import] Erreur', ['error' => $e->getMessage()]);
            return back()->withErrors(['doi' => 'Une erreur est survenue lors de l\'importation.']);
        }
    }

    // ── Approbation d'une soumission (Admin d'axe) ──────────────────────────

    /**
     * Approuve une soumission en attente.
     * RG-011 : Seul l'admin de l'axe concerné peut valider.
     */
    public function approve(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'commentaire_admin' => 'nullable|string|max:2000',
        ]);

        $workflow = WorkflowValidation::with('publication.axe')->findOrFail($id);

        // Vérifier que la soumission est bien en attente
        if (!$workflow->isPending()) {
            return back()->with('error', 'Cette soumission a déjà été traitée.');
        }

        // Vérifier que l'utilisateur est l'admin de l'axe concerné (RG-011)
        $userId = session('user_id');
        $userRole = session('user_role');

        if ($userRole !== User::ROLE_SUPER_ADMIN) {
            $axe = $workflow->publication->axe;
            if ($axe) {
                $isResponsible = ($axe->responsable_id === $userId);
                if (!$isResponsible) {
                    $user = User::find($userId);
                    $isResponsible = $user && ($user->axe_principal_id === $axe->id);
                }
            } else {
                $isResponsible = false;
            }
            if (!$isResponsible) {
                return back()->with('error', 'Seul l\'administrateur de l\'axe concerné peut valider cette soumission.');
            }
        }

        return DB::transaction(function () use ($workflow, $request, $userId) {
            // Mettre à jour le workflow
            $workflow->update([
                'statut'            => WorkflowValidation::STATUS_APPROVED,
                'validateur_id'     => $userId,
                'commentaire_admin' => $request->input('commentaire_admin'),
                'date_decision'     => now(),
            ]);

            // Publier la publication
            $workflow->publication->update([
                'statut'           => Publication::STATUS_PUBLISHED,
                'date_publication' => now(),
            ]);

            // Enregistrer l'historique du workflow
            DB::table('workflow_historique')->insert([
                'id'            => \Illuminate\Support\Str::uuid(),
                'workflow_id'   => $workflow->id,
                'statut_avant'  => WorkflowValidation::STATUS_PENDING,
                'statut_apres'  => WorkflowValidation::STATUS_APPROVED,
                'commentaire'   => $request->input('commentaire_admin'),
                'acteur_id'     => $userId,
                'created_at'    => now(),
            ]);

            // Log d'audit
            AuditLog::log(
                AuditLog::ACTION_APPROVE,
                $userId,
                'publication',
                $workflow->publication_id,
                [
                    'workflow_id' => $workflow->id,
                    'titre'       => $workflow->publication->titre_fr,
                ],
            );

            return back()->with('success', 'Soumission approuvée et publiée.');
        });
    }

    // ── Rejet d'une soumission (Admin d'axe) ────────────────────────────────

    /**
     * Rejette une soumission avec motif obligatoire.
     * RG-014 : Le motif de rejet est obligatoire.
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        if ($request->has('commentaire') && !$request->has('commentaire_admin')) {
            $request->merge(['commentaire_admin' => $request->input('commentaire')]);
        }

        // RG-014 : motif obligatoire
        $request->validate([
            'commentaire_admin' => 'required|string|max:2000',
        ]);

        $workflow = WorkflowValidation::with('publication.axe')->findOrFail($id);

        if (!$workflow->isPending()) {
            return back()->with('error', 'Cette soumission a déjà été traitée.');
        }

        // Vérifier le rôle d'admin d'axe (RG-011)
        $userId = session('user_id');
        $userRole = session('user_role');

        if ($userRole !== User::ROLE_SUPER_ADMIN) {
            $axe = $workflow->publication->axe;
            if ($axe) {
                $isResponsible = ($axe->responsable_id === $userId);
                if (!$isResponsible) {
                    $user = User::find($userId);
                    $isResponsible = $user && ($user->axe_principal_id === $axe->id);
                }
            } else {
                $isResponsible = false;
            }
            if (!$isResponsible) {
                return back()->with('error', 'Seul l\'administrateur de l\'axe concerné peut rejeter cette soumission.');
            }
        }

        return DB::transaction(function () use ($workflow, $request, $userId) {
            // Mettre à jour le workflow
            $workflow->update([
                'statut'            => WorkflowValidation::STATUS_REVISION_REQUIRED,
                'validateur_id'     => $userId,
                'commentaire_admin' => $request->input('commentaire_admin'),
                'date_decision'     => now(),
            ]);

            // Passer la publication en statut 'rejected'
            $workflow->publication->update([
                'statut' => Publication::STATUS_REJECTED,
            ]);

            // Enregistrer l'historique
            DB::table('workflow_historique')->insert([
                'id'            => \Illuminate\Support\Str::uuid(),
                'workflow_id'   => $workflow->id,
                'statut_avant'  => WorkflowValidation::STATUS_PENDING,
                'statut_apres'  => WorkflowValidation::STATUS_REVISION_REQUIRED,
                'commentaire'   => $request->input('commentaire_admin'),
                'acteur_id'     => $userId,
                'created_at'    => now(),
            ]);

            // Log d'audit
            AuditLog::log(
                AuditLog::ACTION_REJECT,
                $userId,
                'publication',
                $workflow->publication_id,
                [
                    'workflow_id' => $workflow->id,
                    'titre'       => $workflow->publication->titre_fr,
                    'motif'       => $request->input('commentaire_admin'),
                ],
            );

            return back()->with('success', 'Soumission rejetée. Le doctorant a été notifié du motif.');
        });
    }

    /**
     * Affiche le formulaire de modification (GET).
     */
    public function edit(string $id): Response
    {
        $publication = Publication::findOrFail($id);

        // Vérification de sécurité/propriétaire — Même le superadmin ne peut modifier que ses propres publications
        $userId = session('user_id');
        if ($publication->auteur_id !== $userId) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier cette publication.');
        }

        // Convertir mots-clés de tableau à chaîne séparée par des virgules
        if (is_array($publication->mots_cles)) {
            $publication->mots_cles = implode(', ', $publication->mots_cles);
        }

        $axes = AxeThematique::actif()->get(['id', 'nom_fr', 'code']);

        $userRole = session('user_role');

        return Inertia::render('Content/Edit', [
            'publication' => $publication,
            'axes'        => $axes,
            'userRole'    => $userRole,
        ]);
    }

    /**
     * Enregistre les modifications (PUT).
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $publication = Publication::findOrFail($id);

        // Vérification de sécurité/propriétaire — Même le superadmin ne peut modifier que ses propres publications
        $userId = session('user_id');
        if ($publication->auteur_id !== $userId) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier cette publication.');
        }

        // Prétraitement de mots_cles s'il est envoyé sous forme de chaîne de caractères
        if ($request->has('mots_cles') && is_string($request->input('mots_cles'))) {
            $motsCles = array_filter(array_map('trim', explode(',', $request->input('mots_cles'))));
            $request->merge(['mots_cles' => $motsCles]);
        }

        $validated = $request->validate([
            'titre_fr'           => 'required|string|max:500',
            'titre_en'           => 'nullable|string|max:500',
            'resume_fr'          => 'required|string',
            'resume_en'          => 'nullable|string',
            'type'               => 'required|in:article,document,event,dataset,news,thesis,report,presentation',
            'axe_id'             => 'required|uuid|exists:axes_thematiques,id',
            'mots_cles'          => 'nullable|array',
            'visibilite'         => 'nullable|in:public,partners,internal',
            'commentaire_auteur' => 'nullable|string|max:2000',
            'fichier'            => 'nullable|file|mimetypes:application/pdf|max:51200',
        ]);

        $user = User::findOrFail($userId);
        $userRole = session('user_role');

        // Seul l'administrateur d'axe (ou super_admin) peut créer/modifier un événement
        if ($validated['type'] === 'event' && !in_array($userRole, ['axe_admin', 'super_admin'])) {
            return back()->withErrors(['type' => 'Seuls les responsables d\'axes peuvent créer ou modifier des événements.']);
        }

        // Seul l'administrateur d'axe (ou super_admin) peut définir le public cible (visibilité restreinte)
        $visibilite = $validated['visibilite'] ?? $publication->visibilite;
        if ($visibilite !== 'public' && !in_array($userRole, ['axe_admin', 'super_admin'])) {
            $visibilite = 'public';
        }

        DB::transaction(function () use ($publication, $validated, $user, $request, $visibilite) {
            // Si c'est un doctorant, la modification remet la publication en soumission/attente (workflow requis)
            $nouveauStatut = $publication->statut;
            if ($user->requiresWorkflow()) {
                $nouveauStatut = Publication::STATUS_SUBMITTED;
            }

            $publication->update([
                'titre_fr'          => $validated['titre_fr'],
                'titre_en'          => $validated['titre_en'] ?? null,
                'resume_fr'         => $validated['resume_fr'],
                'resume_en'         => $validated['resume_en'] ?? null,
                'type'              => $validated['type'],
                'statut'            => $nouveauStatut,
                'visibilite'        => $visibilite,
                'axe_id'            => $validated['axe_id'],
                'mots_cles'         => $validated['mots_cles'] ?? null,
                'updated_at'        => now(),
            ]);

            // Enregistrement / Remplacement du document s'il y en a un nouveau
            if ($request->hasFile('fichier')) {
                $file = $request->file('fichier');
                $path = $file->store('documents', 'minio');

                // Supprimer l'ancien document s'il existait
                $oldDoc = DB::table('documents')->where('publication_id', $publication->id)->first();
                if ($oldDoc) {
                    \Illuminate\Support\Facades\Storage::disk('minio')->delete($oldDoc->fichier_url);
                    DB::table('documents')->where('publication_id', $publication->id)->delete();
                }

                DB::table('documents')->insert([
                    'publication_id'  => $publication->id,
                    'fichier_url'     => $path,
                    'fichier_nom'     => $file->getClientOriginalName(),
                    'fichier_taille'  => $file->getSize(),
                    'fichier_mime'    => $file->getMimeType(),
                    'these_soutenue'  => false,
                ]);
            }

            // Gérer le workflow si c'est un doctorant
            if ($user->requiresWorkflow()) {
                $delaiJours = (int) DB::table('parametres_systeme')
                    ->where('cle', 'workflow_delai_jours')
                    ->value('valeur') ?: 14;

                $wf = WorkflowValidation::where('publication_id', $publication->id)->first();
                if ($wf) {
                    $wf->update([
                        'statut'             => WorkflowValidation::STATUS_PENDING,
                        'commentaire_auteur' => $validated['commentaire_auteur'] ?? null,
                        'version'            => $wf->version + 1,
                        'date_soumission'    => now(),
                        'date_limite'        => now()->addDays($delaiJours),
                    ]);
                } else {
                    WorkflowValidation::create([
                        'publication_id'     => $publication->id,
                        'soumetteur_id'      => $user->id,
                        'statut'             => WorkflowValidation::STATUS_PENDING,
                        'commentaire_auteur' => $validated['commentaire_auteur'] ?? null,
                        'version'            => 1,
                        'date_soumission'    => now(),
                        'date_limite'        => now()->addDays($delaiJours),
                    ]);
                }
            }

            AuditLog::log(
                AuditLog::ACTION_UPDATE,
                $user->id,
                'publication',
                $publication->id,
                ['titre' => $publication->titre_fr],
            );
        });

        return redirect()->route('mes-publications')
            ->with('success', 'Publication mise à jour avec succès.');
    }

    /**
     * Supprime une publication (DELETE).
     */
    public function destroy(string $id): RedirectResponse
    {
        $publication = Publication::findOrFail($id);

        // Vérification de sécurité/propriétaire — Même le superadmin ne peut supprimer que ses propres publications
        $userId = session('user_id');
        if ($publication->auteur_id !== $userId) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer cette publication.');
        }

        DB::transaction(function () use ($publication, $userId) {
            $doc = DB::table('documents')->where('publication_id', $publication->id)->first();
            if ($doc) {
                \Illuminate\Support\Facades\Storage::disk('minio')->delete($doc->fichier_url);
            }

            if ($publication->type === 'dataset') {
                $fichiers = DB::table('datasets_fichiers')->where('dataset_id', $publication->id)->get();
                foreach ($fichiers as $f) {
                    \Illuminate\Support\Facades\Storage::disk('minio')->delete($f->chemin_minio);
                }
            }

            $publication->delete();

            AuditLog::log(
                AuditLog::ACTION_DELETE,
                $userId,
                'publication',
                $publication->id,
                ['titre' => $publication->titre_fr],
            );
        });

        return redirect()->route('mes-publications')
            ->with('success', 'Publication supprimée avec succès.');
    }
}
