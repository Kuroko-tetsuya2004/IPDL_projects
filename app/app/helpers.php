<?php

/**
 * Helpers globaux — Portail UMMISCO
 *
 * Chargé automatiquement via composer.json > autoload > files.
 * Ne pas alourdir ce fichier — uniquement des helpers petits et réutilisables.
 */

if (!function_exists('session_user')) {
    /**
     * Retourne l'utilisateur courant depuis la session Laravel.
     * Utilise le cache de requête pour éviter N+1 sur la même requête HTTP.
     *
     * @return \App\Modules\User\Models\User|null
     */
    function session_user(): ?\App\Modules\User\Models\User
    {
        static $cached = null;

        if ($cached !== null) return $cached;

        $userId = session('user_id');
        if (!$userId) return null;

        $cached = \App\Modules\User\Models\User::find($userId);
        return $cached;
    }
}

if (!function_exists('current_locale')) {
    /**
     * Retourne la locale active (depuis la session ou la config).
     */
    function current_locale(): string
    {
        return session('locale', config('app.locale', 'fr'));
    }
}

if (!function_exists('is_authenticated')) {
    /**
     * Vérifie si un utilisateur est connecté (via session Keycloak).
     */
    function is_authenticated(): bool
    {
        return session()->has('user_id') && session()->has('access_token');
    }
}

if (!function_exists('has_role')) {
    /**
     * Vérifie si l'utilisateur courant a un rôle donné.
     */
    function has_role(string $role): bool
    {
        return session('user_role') === $role;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Vérifie si l'utilisateur courant est admin (super_admin ou axe_admin).
     */
    function is_admin(): bool
    {
        return in_array(session('user_role'), ['super_admin', 'axe_admin']);
    }
}
