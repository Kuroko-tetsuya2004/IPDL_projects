<?php

namespace App\Modules\User\Controllers;

use App\Modules\Content\Models\Publication;
use App\Modules\Content\Models\WorkflowValidation;
use App\Modules\User\Models\AxeThematique;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * DashboardController — Tableau de bord des utilisateurs authentifiés
 *
 * Adapte le contenu selon le rôle de l'utilisateur (session 'user_role') :
 *   - visitor / partner     → statistiques globales
 *   - researcher            → mes publications + draft
 *   - doctoral_student      → soumissions en attente de validation
 *   - axe_admin             → soumissions de mon axe + membres
 *   - super_admin           → vue complète du portail
 */
class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        // Statistiques globales (toujours disponibles)
        $stats = DB::table('v_statistiques_laboratoire')->first();

        // Initialisation du tableau de données à envoyer à Inertia
        $data = [
            'stats'                => $stats,
            'userRole'             => $userRole,
            'mesPublications'      => [],
            'soumissionsEnAttente' => [],
            'axes'                 => [],
            'totalUsers'           => 0,
            'statsStatuts'         => [],
            'publicationsRecentes' => [],
            'axe'                  => null,
            'membres'              => [],
            'profile'              => null,
            'directeur'            => null,
            'coDirecteur'          => null,
            'demandesSuppression'  => [],
        ];

        // ── CAS 1 : SUPER ADMIN ──────────────────────────────────────────────
        if ($userRole === 'super_admin') {
            $data['totalUsers'] = \App\Modules\User\Models\User::count();
            
            $data['soumissionsEnAttente'] = WorkflowValidation::with([
                'publication:id,titre_fr,type,axe_id',
                'publication.axe:id,nom_fr,code',
                'soumetteur:id,nom,prenom',
            ])->pending()->orderBy('date_soumission', 'desc')->limit(10)->get();

            $data['publicationsRecentes'] = Publication::with(['auteur:id,nom,prenom', 'axe:id,code'])
                ->orderBy('created_at', 'desc')->limit(5)->get();

            // Stats d'états
            $data['statsStatuts'] = DB::table('publications')
                ->select('statut', DB::raw('count(*) as total'))
                ->whereNull('deleted_at')
                ->groupBy('statut')
                ->pluck('total', 'statut')
                ->toArray();
                
            $data['axes'] = AxeThematique::actif()
                ->withCount(['publications' => fn ($q) => $q->where('statut', 'published')])
                ->get();
        }

        // ── CAS 2 : AXE ADMIN ────────────────────────────────────────────────
        elseif ($userRole === 'axe_admin') {
            $axe = AxeThematique::where('responsable_id', $userId)->first();
            if (!$axe) {
                $user = \App\Modules\User\Models\User::find($userId);
                if ($user && $user->axe_principal_id) {
                    $axe = AxeThematique::find($user->axe_principal_id);
                }
            }
            $data['axe'] = $axe;

            if ($axe) {
                $data['membres'] = \App\Modules\User\Models\User::where('axe_principal_id', $axe->id)
                    ->orWhereHas('axes', function ($q) use ($axe) {
                        $q->where('axes_thematiques.id', $axe->id);
                    })
                    ->orderBy('nom')
                    ->orderBy('prenom')
                    ->get(['id', 'nom', 'prenom', 'email', 'role']);

                $data['soumissionsEnAttente'] = WorkflowValidation::with([
                    'publication:id,titre_fr,type,axe_id',
                    'soumetteur:id,nom,prenom',
                ])->pending()->byAxe($axe->id)->orderBy('date_soumission', 'desc')->get();

                $data['publicationsRecentes'] = Publication::with(['auteur:id,nom,prenom', 'axe:id,code'])
                    ->where('axe_id', $axe->id)
                    ->orderBy('created_at', 'desc')->limit(5)->get();
            }
        }

        // ── CAS 3 : CHERCHEUR ────────────────────────────────────────────────
        elseif ($userRole === 'researcher') {
            $data['profile'] = DB::table('profils_chercheurs')->where('user_id', $userId)->first();
            
            $data['mesPublications'] = Publication::where('auteur_id', $userId)
                ->with('axe:id,code,nom_fr')
                ->orderBy('updated_at', 'desc')
                ->limit(8)
                ->get(['id', 'titre_fr', 'type', 'statut', 'date_publication', 'created_at', 'axe_id']);
        }

        // ── CAS 4 : DOCTORANT ────────────────────────────────────────────────
        elseif ($userRole === 'doctoral_student') {
            $profile = DB::table('profils_doctorants')->where('user_id', $userId)->first();
            $data['profile'] = $profile;

            if ($profile) {
                $data['directeur'] = $profile->directeur_id ? \App\Modules\User\Models\User::find($profile->directeur_id, ['id', 'nom', 'prenom', 'titre_academique']) : null;
                $data['coDirecteur'] = $profile->co_directeur_id ? \App\Modules\User\Models\User::find($profile->co_directeur_id, ['id', 'nom', 'prenom', 'titre_academique']) : null;
            }

            $data['mesPublications'] = Publication::where('auteur_id', $userId)
                ->with('axe:id,code,nom_fr')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get(['id', 'titre_fr', 'type', 'statut', 'date_publication', 'created_at', 'axe_id']);

            $data['soumissionsEnAttente'] = WorkflowValidation::with([
                'publication:id,titre_fr,type',
            ])
            ->where('soumetteur_id', $userId)
            ->orderBy('date_soumission', 'desc')
            ->limit(5)
            ->get();
        }

        // ── CAS 5 : PARTENAIRE / VISITEUR ────────────────────────────────────
        else {
            $data['publicationsRecentes'] = Publication::where('visibilite', 'public')
                ->where('statut', 'published')
                ->with(['auteur:id,nom,prenom', 'axe:id,code'])
                ->orderBy('date_publication', 'desc')
                ->limit(5)
                ->get();
        }

        // Récupération des demandes de suppression en cours
        $demandes = [];
        if ($userRole === 'super_admin') {
            $demandes = DB::table('demandes_suppression')
                ->join('publications', 'demandes_suppression.publication_id', '=', 'publications.id')
                ->join('users', 'demandes_suppression.propose_par', '=', 'users.id')
                ->where('demandes_suppression.statut', 'pending')
                ->select(
                    'demandes_suppression.*',
                    'publications.titre_fr as publication_titre',
                    'publications.type as publication_type',
                    'publications.axe_id as publication_axe_id',
                    DB::raw("concat(users.prenom, ' ', users.nom) as propose_par_nom")
                )
                ->orderBy('demandes_suppression.created_at', 'desc')
                ->get()
                ->map(function ($demande) {
                    $votersCount = DB::table('users')
                        ->where('axe_principal_id', $demande->publication_axe_id)
                        ->whereIn('role', ['researcher', 'axe_admin'])
                        ->where('statut', 'active')
                        ->count();
                    $votersCount = max($votersCount, 1);
                    $majorityThreshold = floor($votersCount / 2) + 1;

                    $votesPour = DB::table('votes_suppression')
                        ->where('demande_suppression_id', $demande->id)
                        ->where('daccord', true)
                        ->count();

                    $votesContre = DB::table('votes_suppression')
                        ->where('demande_suppression_id', $demande->id)
                        ->where('daccord', false)
                        ->count();

                    $demande->votes_pour = $votesPour;
                    $demande->votes_contre = $votesContre;
                    $demande->total_voters = $votersCount;
                    $demande->seuil = $majorityThreshold;
                    return $demande;
                });
        } elseif (in_array($userRole, ['researcher', 'axe_admin'])) {
            $userAxeId = null;
            if ($userRole === 'axe_admin') {
                $userAxeId = DB::table('axes_thematiques')->where('responsable_id', $userId)->value('id');
            } else {
                $userAxeId = DB::table('users')->where('id', $userId)->value('axe_principal_id');
            }

            if ($userAxeId) {
                $demandes = DB::table('demandes_suppression')
                    ->join('publications', 'demandes_suppression.publication_id', '=', 'publications.id')
                    ->join('users', 'demandes_suppression.propose_par', '=', 'users.id')
                    ->where('demandes_suppression.statut', 'pending')
                    ->where('publications.axe_id', $userAxeId)
                    ->select(
                        'demandes_suppression.*',
                        'publications.titre_fr as publication_titre',
                        'publications.type as publication_type',
                        'publications.axe_id as publication_axe_id',
                        DB::raw("concat(users.prenom, ' ', users.nom) as propose_par_nom")
                    )
                    ->orderBy('demandes_suppression.created_at', 'desc')
                    ->get()
                    ->map(function ($demande) use ($userId) {
                        $votersCount = DB::table('users')
                            ->where('axe_principal_id', $demande->publication_axe_id)
                            ->whereIn('role', ['researcher', 'axe_admin'])
                            ->where('statut', 'active')
                            ->count();
                        $votersCount = max($votersCount, 1);
                        $majorityThreshold = floor($votersCount / 2) + 1;

                        $votesPour = DB::table('votes_suppression')
                            ->where('demande_suppression_id', $demande->id)
                            ->where('daccord', true)
                            ->count();

                        $votesContre = DB::table('votes_suppression')
                            ->where('demande_suppression_id', $demande->id)
                            ->where('daccord', false)
                            ->count();

                        $userVote = DB::table('votes_suppression')
                            ->where('demande_suppression_id', $demande->id)
                            ->where('user_id', $userId)
                            ->value('daccord');

                        $demande->votes_pour = $votesPour;
                        $demande->votes_contre = $votesContre;
                        $demande->total_voters = $votersCount;
                        $demande->seuil = $majorityThreshold;
                        $demande->user_vote = $userVote;
                        return $demande;
                    });
            }
        }

        $data['demandesSuppression'] = $demandes;

        return Inertia::render('Dashboard/Index', $data);
    }
}
