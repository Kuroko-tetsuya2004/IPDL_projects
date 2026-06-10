<?php

namespace App\Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KeycloakMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // ✅ On vérifie uniquement user_id — pas access_token (qui expire)
        if (!session()->has('user_id')) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'Authentification requise.'], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        if (!empty($roles)) {
            $userRole = session('user_role');

            if (!in_array($userRole, $roles)) {
                if ($request->expectsJson() || $request->header('X-Inertia')) {
                    return response()->json(['message' => 'Accès interdit — rôle insuffisant.'], 403);
                }

                abort(403, 'Accès interdit — vous ne disposez pas des droits nécessaires.');
            }
        }

        return $next($request);
    }
}
