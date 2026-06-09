<?php

namespace App\Modules\Admin\Controllers;

use App\Modules\Content\Models\Publication;
use App\Modules\User\Models\AxeThematique;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * AdminController — Back-office Super Admin / Axe Admin
 */
class AdminController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_users'        => User::count(),
            'total_publications' => Publication::count(),
            'total_axes'         => AxeThematique::count(),
            'pending_workflow'   => DB::table('workflow_validations')->where('statut', 'pending')->count(),
        ];

        return Inertia::render('Admin/Index', ['stats' => $stats]);
    }

    public function users(Request $request): Response
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        $query = User::query()
            ->leftJoin('profils_chercheurs', 'users.id', '=', 'profils_chercheurs.user_id')
            ->leftJoin('profils_doctorants', 'users.id', '=', 'profils_doctorants.user_id')
            ->leftJoin('axes_thematiques', 'users.axe_principal_id', '=', 'axes_thematiques.id')
            ->select(
                'users.*',
                'profils_chercheurs.specialite as specialite',
                'profils_doctorants.domaine_expertise as domaine_expertise',
                'axes_thematiques.nom_fr as axe_nom'
            )
            ->orderBy('users.created_at', 'desc');

        if ($userRole === 'axe_admin') {
            $axeId = DB::table('axes_thematiques')->where('responsable_id', $userId)->value('id');
            if (!$axeId) {
                $u = User::find($userId);
                if ($u) {
                    $axeId = $u->axe_principal_id;
                }
            }
            if ($axeId) {
                $query->where(function ($q) use ($axeId) {
                    $q->where('users.axe_principal_id', $axeId)
                      ->orWhereExists(function ($sub) use ($axeId) {
                          $sub->select(DB::raw(1))
                              ->from('users_axes')
                              ->whereColumn('users_axes.user_id', 'users.id')
                              ->where('users_axes.axe_thematique_id', $axeId);
                      });
                })
                ->where('users.role', 'doctoral_student');
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('users.nom', 'ilike', "%$search%")
                  ->orWhere('users.prenom', 'ilike', "%$search%")
                  ->orWhere('users.email', 'ilike', "%$search%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('users.role', $role);
        }

        $users = $query->paginate(20)->withQueryString();

        $axes = DB::table('axes_thematiques')->where('actif', true)->get(['id', 'nom_fr', 'code']);

        return Inertia::render('Admin/Users', [
            'users'   => $users,
            'axes'    => $axes,
            'filters' => $request->only(['q', 'role']),
        ]);
    }

    public function updateUser(Request $request, string $id)
    {
        $currentUserRole = session('user_role');
        $currentUserId   = session('user_id');

        $user = User::findOrFail($id);

        if ($currentUserRole === 'axe_admin') {
            $axeId = DB::table('axes_thematiques')->where('responsable_id', $currentUserId)->value('id');
            if (!$axeId) {
                $currentUser = User::find($currentUserId);
                if ($currentUser) {
                    $axeId = $currentUser->axe_principal_id;
                }
            }
            $isMember = false;
            if ($axeId) {
                $isMember = ($user->axe_principal_id === $axeId) || DB::table('users_axes')
                    ->where('user_id', $user->id)
                    ->where('axe_thematique_id', $axeId)
                    ->exists();
            }
            if ($user->role !== 'doctoral_student' || !$isMember) {
                abort(403, 'Accès interdit — vous ne disposez pas des droits de validation nécessaires pour cet utilisateur.');
            }
        }

        $validated = $request->validate([
            'role'             => 'required|in:visitor,researcher,doctoral_student,partner,axe_admin,super_admin',
            'statut'           => 'required|in:active,inactive,archived,pending',
            'axe_principal_id' => 'nullable|uuid|exists:axes_thematiques,id',
        ]);

        $oldStatus = $user->statut;
        $oldRole   = $user->role;

        $user->update($validated);

        // Synchroniser avec Keycloak si le statut ou le rôle a changé
        $authController = new \App\Modules\Auth\Controllers\AuthController();
        if ($oldStatus === 'pending' && $user->statut === 'active') {
            $authController->enableUserInKeycloak($user);
        }
        if ($oldStatus !== $user->statut || $oldRole !== $user->role) {
            $authController->syncUserRoleInKeycloak($user);
        }

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    public function publications(Request $request): Response
    {
        $userRole = session('user_role');
        $query = Publication::with(['auteur:id,nom,prenom', 'axe:id,nom_fr,code'])
            ->orderBy('created_at', 'desc');

        if ($search = $request->input('q')) {
            $query->where('titre_fr', 'ilike', "%$search%");
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        if ($axeId = $request->input('axe')) {
            $query->where('axe_id', $axeId);
        }

        $publications = $query->paginate(20)->withQueryString();
        $axes = AxeThematique::actif()->get(['id', 'nom_fr', 'code']);

        return Inertia::render('Admin/Publications', [
            'publications' => $publications,
            'axes'         => $axes,
            'filters'      => $request->only(['q', 'statut', 'axe']),
            'userRole'     => $userRole,
        ]);
    }

    public function axes(Request $request): Response
    {
        $axes = AxeThematique::withCount(['publications', 'membres'])
            ->with('responsable:id,nom,prenom')
            ->orderBy('ordre_affichage')
            ->get();

        $users = User::whereIn('role', ['researcher', 'axe_admin'])
            ->where('statut', 'active')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'email']);

        $userRole = session('user_role');

        return Inertia::render('Admin/Axes', [
            'axes'     => $axes,
            'users'    => $users,
            'userRole' => $userRole
        ]);
    }

    public function storeAxe(Request $request)
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Seul le superadmin peut créer des axes thématiques.');
        }

        $validated = $request->validate([
            'code'            => 'required|string|max:50|unique:axes_thematiques,code',
            'nom_fr'          => 'required|string|max:200',
            'nom_en'          => 'nullable|string|max:200',
            'description_fr'  => 'nullable|string',
            'description_en'  => 'nullable|string',
            'couleur_hex'     => 'nullable|string|max:7',
            'ordre_affichage' => 'required|integer',
            'actif'           => 'required|boolean',
            'responsable_id'  => 'nullable|uuid|exists:users,id',
        ]);

        return DB::transaction(function () use ($validated) {
            $axe = AxeThematique::create($validated);

            if ($axe->responsable_id) {
                $responsable = User::findOrFail($axe->responsable_id);
                if ($responsable->role !== 'axe_admin') {
                    $responsable->update(['role' => 'axe_admin']);
                    $authController = new \App\Modules\Auth\Controllers\AuthController();
                    $authController->syncUserRoleInKeycloak($responsable);
                }
            }

            return back()->with('success', 'Axe thématique créé avec succès.');
        });
    }

    public function updateAxe(Request $request, string $id)
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Seul le superadmin peut modifier des axes thématiques.');
        }

        $axe = AxeThematique::findOrFail($id);

        $validated = $request->validate([
            'code'            => 'required|string|max:50|unique:axes_thematiques,code,' . $id,
            'nom_fr'          => 'required|string|max:200',
            'nom_en'          => 'nullable|string|max:200',
            'description_fr'  => 'nullable|string',
            'description_en'  => 'nullable|string',
            'couleur_hex'     => 'nullable|string|max:7',
            'ordre_affichage' => 'required|integer',
            'actif'           => 'required|boolean',
            'responsable_id'  => 'nullable|uuid|exists:users,id',
        ]);

        $oldResponsableId = $axe->responsable_id;
        $newResponsableId = $validated['responsable_id'];

        return DB::transaction(function () use ($axe, $validated, $oldResponsableId, $newResponsableId) {
            $axe->update($validated);

            if ($oldResponsableId !== $newResponsableId) {
                if ($oldResponsableId) {
                    $oldResponsable = User::find($oldResponsableId);
                    if ($oldResponsable) {
                        $managesOtherAxes = AxeThematique::where('responsable_id', $oldResponsableId)
                            ->where('id', '!=', $axe->id)
                            ->exists();

                        if (!$managesOtherAxes && $oldResponsable->role === 'axe_admin') {
                            $oldResponsable->update(['role' => 'researcher']);
                            $authController = new \App\Modules\Auth\Controllers\AuthController();
                            $authController->syncUserRoleInKeycloak($oldResponsable);
                        }
                    }
                }

                if ($newResponsableId) {
                    $newResponsable = User::findOrFail($newResponsableId);
                    if ($newResponsable->role !== 'axe_admin') {
                        $newResponsable->update(['role' => 'axe_admin']);
                        $authController = new \App\Modules\Auth\Controllers\AuthController();
                        $authController->syncUserRoleInKeycloak($newResponsable);
                    }
                }
            }

            return back()->with('success', 'Axe thématique mis à jour.');
        });
    }

    public function parametres(): Response
    {
        $params = DB::table('parametres_systeme')
            ->orderBy('cle')
            ->get(['cle', 'valeur', 'description', 'modifiable']);

        return Inertia::render('Admin/Parametres', ['parametres' => $params]);
    }

    public function updateParametre(Request $request, string $cle)
    {
        $validated = $request->validate(['valeur' => 'required|string|max:500']);

        DB::table('parametres_systeme')
            ->where('cle', $cle)
            ->update(['valeur' => $validated['valeur'], 'updated_at' => now()]);

        return back()->with('success', "Paramètre « {$cle} » mis à jour.");
    }

    public function statistiques(): Response
    {
        $stats = DB::table('v_statistiques_laboratoire')->first();
        $pubParMois = DB::select("
            SELECT DATE_TRUNC('month', date_publication)::date AS mois,
                   COUNT(*) AS total
            FROM publications
            WHERE statut = 'published' AND date_publication IS NOT NULL
            GROUP BY 1 ORDER BY 1 DESC LIMIT 12
        ");

        return Inertia::render('Admin/Statistiques', [
            'stats'     => $stats,
            'pubParMois' => $pubParMois,
        ]);
    }

    public function acl(): Response
    {
        $acls = DB::table('controle_acces')
            ->leftJoin('users', 'controle_acces.accordé_par', '=', 'users.id')
            ->select('controle_acces.*', 'users.nom', 'users.prenom', 'users.email')
            ->orderBy('controle_acces.created_at', 'desc')
            ->paginate(20)
            ->through(function ($item) {
                $permissions = $item->permissions;
                if (is_string($permissions) && str_starts_with($permissions, '{') && str_ends_with($permissions, '}')) {
                    $content = substr($permissions, 1, -1);
                    if (empty($content)) {
                        $item->permissions = [];
                    } else {
                        preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|([^,]+)/', $content, $matches);
                        $result = [];
                        for ($i = 0; $i < count($matches[0]); $i++) {
                            if ($matches[1][$i] !== '') {
                                $result[] = stripcslashes($matches[1][$i]);
                            } else {
                                $result[] = trim($matches[2][$i]);
                            }
                        }
                        $item->permissions = $result;
                    }
                } elseif (is_string($permissions)) {
                    $item->permissions = json_decode($permissions, true) ?: [$permissions];
                }
                return $item;
            });

        return Inertia::render('Admin/Acl', ['acls' => $acls]);
    }

    public function datasets(Request $request): Response
    {
        $query = Publication::where('type', 'dataset')
            ->with(['auteur:id,nom,prenom', 'axe:id,nom_fr,code', 'dataset'])
            ->orderBy('created_at', 'desc');

        if ($search = $request->input('q')) {
            $query->where('titre_fr', 'ilike', "%$search%");
        }

        if ($axeId = $request->input('axe')) {
            $query->where('axe_id', $axeId);
        }

        $datasets = $query->paginate(20)->withQueryString();
        $axes = AxeThematique::actif()->get(['id', 'nom_fr', 'code']);

        return Inertia::render('Admin/Datasets', [
            'datasets' => $datasets,
            'axes'     => $axes,
            'filters'  => $request->only(['q', 'axe']),
        ]);
    }

    public function deleteDataset(string $id)
    {
        $dataset = Publication::where('type', 'dataset')->findOrFail($id);
        
        if ($dataset->auteur_id !== session('user_id')) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer ce dataset car vous n\'en êtes pas l\'auteur.');
        }
        
        DB::transaction(function () use ($dataset) {
            $dataset->delete();

            \App\Modules\Audit\Models\AuditLog::log(
                \App\Modules\Audit\Models\AuditLog::ACTION_DELETE,
                session('user_id'),
                'dataset',
                $dataset->id,
                ['titre' => $dataset->titre_fr],
            );
        });

        return back()->with('success', 'Dataset supprimé avec succès.');
    }

    public function proposerSuppression(Request $request, string $id)
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Seul le superadmin peut proposer la suppression d\'une publication.');
        }

        $validated = $request->validate([
            'motif' => 'required|string|max:2000'
        ]);

        $publication = Publication::findOrFail($id);

        if ($publication->auteur_id === session('user_id')) {
            DB::transaction(function () use ($publication) {
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
                
                \App\Modules\Audit\Models\AuditLog::log(
                    \App\Modules\Audit\Models\AuditLog::ACTION_DELETE,
                    session('user_id'),
                    'publication',
                    $publication->id,
                    ['titre' => $publication->titre_fr, 'mode' => 'direct_author']
                );
            });
            return back()->with('success', 'Votre publication a été supprimée directement.');
        }

        $existing = DB::table('demandes_suppression')
            ->where('publication_id', $id)
            ->where('statut', 'pending')
            ->exists();

        if ($existing) {
            return back()->with('error', 'Une demande de suppression est déjà en cours pour cette publication.');
        }

        DB::transaction(function () use ($id, $validated, $publication) {
            DB::table('demandes_suppression')->insert([
                'id'             => \Illuminate\Support\Str::uuid(),
                'publication_id' => $id,
                'propose_par'    => session('user_id'),
                'motif'          => $validated['motif'],
                'statut'         => 'pending',
                'created_at'     => now(),
                'updated_at'     => now()
            ]);

            $axeId = $publication->axe_id;
            $userIdsToNotify = [];

            if ($publication->auteur_id) {
                $userIdsToNotify[] = $publication->auteur_id;
            }

            if ($axeId) {
                $axisMembers = DB::table('users')
                    ->where('axe_principal_id', $axeId)
                    ->where('statut', 'active')
                    ->pluck('id')
                    ->toArray();

                $pivotMembers = DB::table('users_axes')
                    ->join('users', 'users_axes.user_id', '=', 'users.id')
                    ->where('users_axes.axe_thematique_id', $axeId)
                    ->where('users.statut', 'active')
                    ->pluck('users.id')
                    ->toArray();

                $userIdsToNotify = array_merge($userIdsToNotify, $axisMembers, $pivotMembers);
            }

            $userIdsToNotify = array_unique($userIdsToNotify);

            foreach ($userIdsToNotify as $recipientId) {
                $isAuthor = ($recipientId === $publication->auteur_id);
                $sujet = $isAuthor ? 'Votre publication fait l\'objet d\'une demande de suppression' : 'Vote requis : Suppression de publication';
                $contenu = $isAuthor 
                    ? 'Le superadmin a proposé la suppression de votre publication "' . $publication->titre_fr . '". Motif : ' . $validated['motif'] . '.'
                    : 'Le superadmin propose la suppression de la publication "' . $publication->titre_fr . '" dans votre axe. Motif : ' . $validated['motif'] . '. Veuillez voter sur votre tableau de bord.';

                DB::table('notifications')->insert([
                    'id'              => \Illuminate\Support\Str::uuid(),
                    'destinataire_id' => $recipientId,
                    'type'            => 'workflow',
                    'canal'           => 'email',
                    'statut'          => 'pending',
                    'sujet'           => $sujet,
                    'contenu'         => $contenu,
                    'created_at'      => now(),
                ]);
            }
        });

        return back()->with('success', 'Proposition de suppression soumise au vote des chercheurs de l\'axe.');
    }

    public function voterSuppression(Request $request, string $id)
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        if (!in_array($userRole, ['researcher', 'axe_admin'])) {
            abort(403, 'Seuls les chercheurs et responsables d\'axe peuvent voter.');
        }

        $demande = DB::table('demandes_suppression')
            ->join('publications', 'demandes_suppression.publication_id', '=', 'publications.id')
            ->where('demandes_suppression.id', $id)
            ->select('demandes_suppression.*', 'publications.axe_id')
            ->first();

        if (!$demande || $demande->statut !== 'pending') {
            return back()->with('error', 'Cette demande de suppression n\'est plus active.');
        }

        $userAxeId = null;
        if ($userRole === 'axe_admin') {
            $userAxeId = DB::table('axes_thematiques')->where('responsable_id', $userId)->value('id');
            if (!$userAxeId) {
                $userAxeId = DB::table('users')->where('id', $userId)->value('axe_principal_id');
            }
        } else {
            $userAxeId = DB::table('users')->where('id', $userId)->value('axe_principal_id');
        }

        if ($userAxeId !== $demande->axe_id) {
            abort(403, 'Vous ne faites pas partie de l\'axe thématique concerné.');
        }

        $validated = $request->validate([
            'daccord' => 'required|boolean'
        ]);

        DB::transaction(function () use ($demande, $userId, $validated) {
            DB::table('votes_suppression')->updateOrInsert(
                ['demande_suppression_id' => $demande->id, 'user_id' => $userId],
                ['daccord' => $validated['daccord'], 'updated_at' => now()]
            );

            $totalChercheurs = DB::table('users')
                ->where('axe_principal_id', $demande->axe_id)
                ->whereIn('role', ['researcher', 'axe_admin'])
                ->where('statut', 'active')
                ->count();

            $votersCount = max($totalChercheurs, 1);
            $majorityThreshold = floor($votersCount / 2) + 1;

            $votesPour = DB::table('votes_suppression')
                ->where('demande_suppression_id', $demande->id)
                ->where('daccord', true)
                ->count();

            $votesContre = DB::table('votes_suppression')
                ->where('demande_suppression_id', $demande->id)
                ->where('daccord', false)
                ->count();

            if ($votesPour >= $majorityThreshold) {
                $publication = Publication::findOrFail($demande->publication_id);
                
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

                DB::table('demandes_suppression')
                    ->where('id', $demande->id)
                    ->update([
                        'statut'     => 'approved',
                        'updated_at' => now()
                    ]);

                \App\Modules\Audit\Models\AuditLog::log(
                    \App\Modules\Audit\Models\AuditLog::ACTION_DELETE,
                    $demande->propose_par,
                    'publication',
                    $demande->publication_id,
                    ['titre' => $publication->titre_fr, 'motif' => $demande->motif, 'workflow' => 'majority_vote']
                );
            } elseif ($votesContre > ($votersCount - $majorityThreshold)) {
                DB::table('demandes_suppression')
                    ->where('id', $demande->id)
                    ->update([
                        'statut'     => 'rejected',
                        'updated_at' => now()
                    ]);
            }
        });

        return back()->with('success', 'Votre vote a été enregistré avec succès.');
    }
}
