<?php

namespace App\Modules\Notification\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        $userId   = session('user_id');
        $userRole = session('user_role');

        $notifications = DB::table('notifications')
            ->select('id', 'type', 'sujet as titre', 'contenu as message', 'created_at', 'lue_le as lu_at')
            ->where('destinataire_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadNotificationsCount = DB::table('notifications')
            ->where('destinataire_id', $userId)
            ->whereNull('lue_le')
            ->count();

        $pendingValidations = [];
        if ($userRole === 'super_admin') {
            $pendingValidations = DB::table('workflow_validations')
                ->join('publications', 'workflow_validations.publication_id', '=', 'publications.id')
                ->join('users', 'workflow_validations.soumetteur_id', '=', 'users.id')
                ->where('workflow_validations.statut', 'pending')
                ->select(
                    'workflow_validations.id',
                    'workflow_validations.publication_id',
                    'publications.titre_fr as titre',
                    'publications.type',
                    DB::raw("concat(users.prenom, ' ', users.nom) as auteur"),
                    'workflow_validations.date_soumission as created_at'
                )
                ->get();
        } elseif ($userRole === 'axe_admin') {
            $axeId = DB::table('axes_thematiques')->where('responsable_id', $userId)->value('id');
            if ($axeId) {
                $pendingValidations = DB::table('workflow_validations')
                    ->join('publications', 'workflow_validations.publication_id', '=', 'publications.id')
                    ->join('users', 'workflow_validations.soumetteur_id', '=', 'users.id')
                    ->where('workflow_validations.statut', 'pending')
                    ->where('publications.axe_id', $axeId)
                    ->select(
                        'workflow_validations.id',
                        'workflow_validations.publication_id',
                        'publications.titre_fr as titre',
                        'publications.type',
                        DB::raw("concat(users.prenom, ' ', users.nom) as auteur"),
                        'workflow_validations.date_soumission as created_at'
                    )
                    ->get();
            }
        }

        $pendingVotes = [];
        if (in_array($userRole, ['researcher', 'axe_admin'])) {
            $userAxeId = null;
            if ($userRole === 'axe_admin') {
                $userAxeId = DB::table('axes_thematiques')->where('responsable_id', $userId)->value('id');
            } else {
                $userAxeId = DB::table('users')->where('id', $userId)->value('axe_principal_id');
            }

            if ($userAxeId) {
                $pendingVotes = DB::table('demandes_suppression')
                    ->join('publications', 'demandes_suppression.publication_id', '=', 'publications.id')
                    ->join('users', 'demandes_suppression.propose_par', '=', 'users.id')
                    ->where('demandes_suppression.statut', 'pending')
                    ->where('publications.axe_id', $userAxeId)
                    ->whereNotExists(function ($query) use ($userId) {
                        $query->select(DB::raw(1))
                            ->from('votes_suppression')
                            ->whereRaw('votes_suppression.demande_suppression_id = demandes_suppression.id')
                            ->where('votes_suppression.user_id', $userId);
                    })
                    ->select(
                        'demandes_suppression.id',
                        'publications.titre_fr as titre',
                        'demandes_suppression.motif',
                        DB::raw("concat(users.prenom, ' ', users.nom) as propose_par_nom"),
                        'demandes_suppression.created_at'
                    )
                    ->get();
            }
        }

        $totalUnreadCount = $unreadNotificationsCount + count($pendingValidations) + count($pendingVotes);

        return Inertia::render('Notification/Index', [
            'notifications'      => $notifications,
            'unreadCount'        => $totalUnreadCount,
            'pendingValidations' => $pendingValidations,
            'pendingVotes'       => $pendingVotes,
        ]);
    }

    public function markRead(string $id)
    {
        $userId = session('user_id');

        DB::table('notifications')
            ->where('id', $id)
            ->where('destinataire_id', $userId)
            ->update(['lue_le' => now()]);

        return back()->with('success', 'Notification marquée comme lue.');
    }

    public function markAllRead()
    {
        $userId = session('user_id');

        DB::table('notifications')
            ->where('destinataire_id', $userId)
            ->whereNull('lue_le')
            ->update(['lue_le' => now()]);

        return back()->with('success', 'Toutes les notifications marquées comme lues.');
    }
}
