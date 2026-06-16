<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Determine the root template that is loaded on the first page visit.
     */
    public function rootView(Request $request): string
    {
        if ($request->is('admin*')) {
            return 'admin';
        }

        if ($request->is('dashboard') || 
            $request->is('mes-publications') || 
            $request->is('publications/soumettre') || 
            $request->is('mes-datasets') || 
            $request->is('soumissions') ||
            $request->is('notifications') ||
            $request->is('profile')) {
            return 'dashboard';
        }

        return 'public';
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request) ? parent::version($request) . '-' . $this->rootView($request) : $this->rootView($request);
    }

    /**
     * Define the props that are shared by default.
     * Ces données sont disponibles dans TOUTES les pages Vue via usePage().props
     */
    public function share(Request $request): array
    {
        $userId   = session('user_id');
        $userRole = session('user_role');
        $userName = session('user_name');
        $locale   = session('locale', 'fr');

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $userId ? [
                    'id'       => $userId,
                    'name'     => $userName,
                    'email'    => session('user_email'),
                    'role'     => $userRole,
                    'orcid_id' => \App\Modules\User\Models\User::find($userId)?->orcid_id,
                ] : null,
                'authenticated' => !is_null($userId),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info'    => $request->session()->get('info'),
            ],
            'locale' => $locale,
            'unread_count' => function () use ($userId, $userRole) {
                if (!$userId) return 0;
                
                $unreadNotifications = \Illuminate\Support\Facades\DB::table('notifications')
                    ->where('destinataire_id', $userId)
                    ->whereNull('lue_le')
                    ->count();

                $pendingValidations = 0;
                if ($userRole === 'super_admin') {
                    $pendingValidations = \Illuminate\Support\Facades\DB::table('workflow_validations')
                        ->where('statut', 'pending')
                        ->count();
                } elseif ($userRole === 'axe_admin') {
                    $axeId = \Illuminate\Support\Facades\DB::table('axes_thematiques')->where('responsable_id', $userId)->value('id');
                    if ($axeId) {
                        $pendingValidations = \Illuminate\Support\Facades\DB::table('workflow_validations')
                            ->join('publications', 'workflow_validations.publication_id', '=', 'publications.id')
                            ->where('workflow_validations.statut', 'pending')
                            ->where('publications.axe_id', $axeId)
                            ->count();
                    }
                }

                $pendingVotes = 0;
                if (in_array($userRole, ['researcher', 'axe_admin'])) {
                    $userAxeId = null;
                    if ($userRole === 'axe_admin') {
                        $userAxeId = \Illuminate\Support\Facades\DB::table('axes_thematiques')->where('responsable_id', $userId)->value('id');
                    } else {
                        $userAxeId = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->value('axe_principal_id');
                    }

                    if ($userAxeId) {
                        $pendingVotes = \Illuminate\Support\Facades\DB::table('demandes_suppression')
                            ->join('publications', 'demandes_suppression.publication_id', '=', 'publications.id')
                            ->where('demandes_suppression.statut', 'pending')
                            ->where('publications.axe_id', $userAxeId)
                            ->whereNotExists(function ($query) use ($userId) {
                                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                                    ->from('votes_suppression')
                                    ->whereRaw('votes_suppression.demande_suppression_id = demandes_suppression.id')
                                    ->where('votes_suppression.user_id', $userId);
                            })
                            ->count();
                    }
                }

                return $unreadNotifications + $pendingValidations + $pendingVotes;
            },
            'ziggy' => fn () => [
                ...(new \Tighten\Ziggy\Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ]);
    }
}
