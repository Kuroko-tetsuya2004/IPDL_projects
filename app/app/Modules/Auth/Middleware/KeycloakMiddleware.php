<?php

namespace App\Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * KeycloakMiddleware — Protection des routes sécurisées
 *
 * Vérifie que l'utilisateur dispose d'une session active et
 * optionnellement d'un rôle spécifique pour accéder à la route.
 *
 * Usage dans les routes :
 *   Route::middleware([KeycloakMiddleware::class])           → vérifie juste l'authentification
 *   Route::middleware(['role:axe_admin,super_admin'])        → vérifie le rôle
 */
class KeycloakMiddleware
{
    /**
     * Vérifie que l'utilisateur est authentifié via session.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Vérifier la présence d'une session active
        if (!session()->has('user_id') || !session()->has('access_token')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Authentification requise.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Si des rôles spécifiques sont requis, vérifier
        if (!empty($roles)) {
            $userRole = session('user_role');

            if (!in_array($userRole, $roles)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Accès interdit — rôle insuffisant.',
                    ], 403);
                }

                abort(403, 'Accès interdit — vous ne disposez pas des droits nécessaires.');
            }
        }

        return $next($request);
    }
}
