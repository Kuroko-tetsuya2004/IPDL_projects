<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\User\Models\User;
use App\Modules\User\Models\AxeThematique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

/**
 * AuthController — Gestion de l'authentification Keycloak OIDC et inscriptions locales
 */
class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Gère la connexion locale et l'authentification Keycloak.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $accessToken = $this->authenticateWithKeycloak($validated['email'], $validated['password']);

        $user = User::where('email', $validated['email'])->first();

        if (!$accessToken && ($validated['email'] === 'directeur@ucad.edu.sn' || $validated['email'] === 'directeur')) {
            if ($user && Hash::check($validated['password'], $user->password)) {
                $this->registerUserInKeycloak($user, $validated['password']);
                $this->enableUserInKeycloak($user);
                $accessToken = $this->authenticateWithKeycloak($validated['email'], $validated['password']);
            }
        }

        if (!$accessToken) {
            return back()->withErrors([
                'email' => 'Identifiants incorrects ou serveur Keycloak indisponible.',
            ])->withInput($request->only('email'));
        }

        if (!$user) {
            $user = User::create([
                'keycloak_id'         => 'keycloak-sync-' . uniqid(),
                'email'               => $validated['email'],
                'nom'                 => 'UMMISCO',
                'prenom'              => 'Membre',
                'role'                => 'visitor',
                'statut'              => 'active',
                'password'            => Hash::make($validated['password']),
                'langue_preference'   => 'fr',
                'email_notifications' => true,
            ]);
        }

        if ($user->statut === 'pending') {
            return back()->withErrors([
                'email' => 'Votre compte est en attente de validation par un administrateur.',
            ])->withInput($request->only('email'));
        }

        if ($user->statut !== 'active') {
            return back()->withErrors([
                'email' => 'Votre compte est inactif ou archivé.',
            ])->withInput($request->only('email'));
        }

        $this->initSession($user, $accessToken);

        $user->update(['derniere_connexion' => now()]);

        AuditLog::log(
            AuditLog::ACTION_LOGIN,
            $user->id,
            'user',
            $user->id,
            ['mode' => 'keycloak_direct', 'role' => $user->role],
        );

        return redirect()->route('dashboard')
            ->with('success', "Bienvenue, {$user->nom_complet} !");
    }

    /**
     * Affiche le formulaire d'inscription.
     */
    public function showRegister()
    {
        $axes = AxeThematique::actif()->get(['id', 'nom_fr', 'code']);
        return view('auth.register', compact('axes'));
    }

    /**
     * Gère l'inscription locale et crée le compte dans Keycloak.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prenom'            => 'required|string|max:100',
            'nom'               => 'required|string|max:100',
            'email'             => 'required|email|max:255|unique:users,email',
            'password'          => 'required|string|min:6|confirmed',
            'role'              => 'required|in:researcher,doctoral_student',
            'orcid_id'          => 'nullable|string|regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/',
            'specialite'        => 'required_if:role,researcher|nullable|string|max:200',
            'domaine_expertise' => 'required_if:role,doctoral_student|nullable|string|max:200',
            'axe_principal_id'  => 'required_if:role,doctoral_student|nullable|uuid|exists:axes_thematiques,id',
        ]);

        return DB::transaction(function () use ($validated) {
            $isAutoApproved = false;

            if (!empty($validated['orcid_id'])) {
                try {
                    $openAlex = app(\App\Modules\Integration\Services\OpenAlexService::class);
                    $works = $openAlex->searchByOrcid($validated['orcid_id'], 10);
                    
                    foreach ($works as $work) {
                        $rawAuthors = $work['raw_data']['authorships'] ?? [];
                        foreach ($rawAuthors as $authorship) {
                            $institutions = $authorship['institutions'] ?? [];
                            foreach ($institutions as $inst) {
                                $name = strtolower($inst['display_name'] ?? '');
                                if (str_contains($name, 'ummisco') || 
                                    str_contains($name, 'cheikh anta diop') || 
                                    str_contains($name, 'ucad') || 
                                    str_contains($name, 'ird') ||
                                    str_contains($name, 'institut de recherche pour le développement') ||
                                    str_contains($name, 'ecole supérieure polytechnique')) {
                                    $isAutoApproved = true;
                                    break 3;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Erreur lors de la validation ORCID pour {$validated['email']} : " . $e->getMessage());
                }
            }

            $user = User::create([
                'keycloak_id'         => 'local-register-' . uniqid(),
                'prenom'              => $validated['prenom'],
                'nom'                 => $validated['nom'],
                'email'               => $validated['email'],
                'role'                => $validated['role'],
                'statut'              => $isAutoApproved ? 'active' : 'pending',
                'password'            => Hash::make($validated['password']),
                'orcid_id'            => $validated['orcid_id'] ?? null,
                'axe_principal_id'    => $validated['role'] === 'doctoral_student' ? $validated['axe_principal_id'] : null,
                'langue_preference'   => 'fr',
                'email_notifications' => true,
            ]);

            if ($validated['role'] === 'researcher') {
                DB::table('profils_chercheurs')->insert([
                    'user_id'         => $user->id,
                    'specialite'      => $validated['specialite'],
                    'nb_publications' => 0,
                    'updated_at'      => now(),
                ]);
            } elseif ($validated['role'] === 'doctoral_student') {
                DB::table('profils_doctorants')->insert([
                    'user_id'           => $user->id,
                    'domaine_expertise' => $validated['domaine_expertise'],
                    'updated_at'        => now(),
                ]);
            }

            $this->registerUserInKeycloak($user, $validated['password']);

            if ($isAutoApproved) {
                $this->enableUserInKeycloak($user);

                $accessToken = $this->authenticateWithKeycloak($validated['email'], $validated['password']);
                if ($accessToken) {
                    $this->initSession($user, $accessToken);
                    $user->update(['derniere_connexion' => now()]);

                    AuditLog::log(
                        AuditLog::ACTION_LOGIN,
                        $user->id,
                        'user',
                        $user->id,
                        ['mode' => 'register_auto_login', 'role' => $user->role],
                    );

                    return redirect()->route('dashboard')
                        ->with('success', "Inscription validée automatiquement grâce à votre affiliation UMMISCO (via ORCID). Bienvenue, {$user->nom_complet} !");
                }
            }

            return redirect()->route('login')
                ->with('success', 'Votre inscription a été enregistrée avec succès. Elle est maintenant en attente de validation par un administrateur.');
        });
    }

    /**
     * ✅ CORRIGÉ — Déconnecte l'utilisateur proprement.
     */
    public function logout(Request $request)
    {
        $userId = session('user_id');

        if ($userId) {
            AuditLog::log(
                AuditLog::ACTION_LOGOUT,
                $userId,
            );
        }

        // ✅ invalidate() d'abord — détruit la session et régénère l'ID
        // ✅ regenerateToken() ensuite — génère un nouveau token CSRF propre
        // ❌ Session::flush() supprimé — causait la perte du token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ✅ Réponse correcte pour Inertia (full page reload vers login)
        if ($request->hasHeader('X-Inertia')) {
            return \Inertia\Inertia::location(route('login'));
        }

        return redirect()->route('login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Initialise la session Laravel avec les données utilisateur.
     */
    private function initSession(User $user, string $accessToken): void
    {
        Session::put([
            'user_id'      => $user->id,
            'user_role'    => $user->role,
            'user_email'   => $user->email,
            'user_name'    => $user->nom_complet,
            'access_token' => $accessToken,
            'locale'       => $user->langue_preference,
        ]);
    }

    // ── Keycloak API Helper Methods ──────────────────────────────────────────

    /**
     * Authentification Direct Access Grant auprès de Keycloak.
     */
    private function authenticateWithKeycloak(string $email, string $password): ?string
    {
        $baseUrl      = env('KEYCLOAK_BASE_URL', 'http://keycloak:8080');
        $realm        = env('KEYCLOAK_REALM', 'ummisco');
        $clientId     = env('KEYCLOAK_CLIENT_ID', 'laravel-app');
        $clientSecret = env('KEYCLOAK_CLIENT_SECRET');

        try {
            $response = Http::asForm()->post("{$baseUrl}/realms/{$realm}/protocol/openid-connect/token", [
                'grant_type'    => 'password',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'username'      => $email,
                'password'      => $password,
                'scope'         => 'openid',
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }
        } catch (\Exception $e) {
            Log::error('Keycloak authentication request failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Récupère un token d'administrateur Keycloak.
     */
    private function getKeycloakAdminToken(): ?string
    {
        $baseUrl       = env('KEYCLOAK_BASE_URL', 'http://keycloak:8080');
        $adminUser     = env('KEYCLOAK_ADMIN', 'admin');
        $adminPassword = env('KEYCLOAK_ADMIN_PASSWORD', 'admin_secret');

        try {
            $response = Http::asForm()->post("{$baseUrl}/realms/master/protocol/openid-connect/token", [
                'grant_type' => 'password',
                'client_id'  => 'admin-cli',
                'username'   => $adminUser,
                'password'   => $adminPassword,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }
        } catch (\Exception $e) {
            Log::error('Keycloak admin token request failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Crée un utilisateur désactivé dans Keycloak avec son rôle.
     */
    public function registerUserInKeycloak(User $user, string $plainPassword): bool
    {
        $adminToken = $this->getKeycloakAdminToken();
        if (!$adminToken) {
            Log::error('Keycloak error: Failed to obtain admin token for user registration.');
            return false;
        }

        $baseUrl = env('KEYCLOAK_BASE_URL', 'http://keycloak:8080');
        $realm   = env('KEYCLOAK_REALM', 'ummisco');

        try {
            $response = Http::withToken($adminToken)->post("{$baseUrl}/admin/realms/{$realm}/users", [
                'username'      => $user->email,
                'email'         => $user->email,
                'firstName'     => $user->prenom,
                'lastName'      => $user->nom,
                'enabled'       => false,
                'emailVerified' => true,
                'credentials'   => [
                    [
                        'type'      => 'password',
                        'value'     => $plainPassword,
                        'temporary' => false,
                    ]
                ],
            ]);

            $keycloakId = null;

            if (!$response->successful() && $response->status() !== 201) {
                if ($response->status() === 409) {
                    Log::warning('Keycloak User already exists, attempting to link: ' . $user->email);
                } else {
                    Log::error('Keycloak User creation failed: ' . $response->body());
                    return false;
                }
            } else {
                $location = $response->header('Location');
                if ($location) {
                    $parts = explode('/', $location);
                    $keycloakId = end($parts);
                }
            }

            if (!$keycloakId) {
                $searchResponse = Http::withToken($adminToken)->get("{$baseUrl}/admin/realms/{$realm}/users", [
                    'email' => $user->email,
                ]);
                if ($searchResponse->successful() && !empty($searchResponse->json())) {
                    $keycloakId = $searchResponse->json()[0]['id'];
                }
            }

            if ($keycloakId) {
                $user->update(['keycloak_id' => $keycloakId]);

                if (!$response->successful() && $response->status() === 409) {
                    $this->resetKeycloakPassword($user, $plainPassword);
                }

                $roleName = $user->role;
                $roleRepResponse = Http::withToken($adminToken)->get("{$baseUrl}/admin/realms/{$realm}/roles/{$roleName}");
                if ($roleRepResponse->successful()) {
                    $roleRep = $roleRepResponse->json();
                    Http::withToken($adminToken)->post("{$baseUrl}/admin/realms/{$realm}/users/{$keycloakId}/role-mappings/realm", [
                        $roleRep,
                    ]);
                }
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Keycloak error during user registration: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur dans Keycloak.
     */
    public function resetKeycloakPassword(User $user, string $plainPassword): bool
    {
        $adminToken = $this->getKeycloakAdminToken();
        if (!$adminToken) {
            Log::error('Keycloak error: Failed to obtain admin token to reset password.');
            return false;
        }

        $baseUrl    = env('KEYCLOAK_BASE_URL', 'http://keycloak:8080');
        $realm      = env('KEYCLOAK_REALM', 'ummisco');
        $keycloakId = $user->keycloak_id;

        if (!$keycloakId || str_starts_with($keycloakId, 'local-register-')) {
            Log::error('Keycloak ID missing or invalid for password reset: ' . $user->email);
            return false;
        }

        try {
            $response = Http::withToken($adminToken)->put("{$baseUrl}/admin/realms/{$realm}/users/{$keycloakId}/reset-password", [
                'type'      => 'password',
                'value'     => $plainPassword,
                'temporary' => false,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Keycloak error during password reset: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Active le compte utilisateur dans Keycloak.
     */
    public function enableUserInKeycloak(User $user): bool
    {
        $adminToken = $this->getKeycloakAdminToken();
        if (!$adminToken) {
            Log::error('Keycloak error: Failed to obtain admin token to enable user.');
            return false;
        }

        $baseUrl    = env('KEYCLOAK_BASE_URL', 'http://keycloak:8080');
        $realm      = env('KEYCLOAK_REALM', 'ummisco');
        $keycloakId = $user->keycloak_id;

        if (!$keycloakId || str_starts_with($keycloakId, 'local-register-')) {
            Log::error('Keycloak ID missing or invalid for user: ' . $user->email);
            return false;
        }

        try {
            $response = Http::withToken($adminToken)->put("{$baseUrl}/admin/realms/{$realm}/users/{$keycloakId}", [
                'enabled' => true,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Keycloak error during enable user: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Synchronise le rôle d'un utilisateur dans Keycloak.
     */
    public function syncUserRoleInKeycloak(User $user): bool
    {
        $adminToken = $this->getKeycloakAdminToken();
        if (!$adminToken) {
            Log::error('Keycloak error: Failed to obtain admin token to sync roles.');
            return false;
        }

        $baseUrl    = env('KEYCLOAK_BASE_URL', 'http://keycloak:8080');
        $realm      = env('KEYCLOAK_REALM', 'ummisco');
        $keycloakId = $user->keycloak_id;

        if (!$keycloakId || str_starts_with($keycloakId, 'local-register-')) {
            Log::error('Keycloak ID missing or invalid for user role sync: ' . $user->email);
            return false;
        }

        try {
            $rolesList = ['visitor', 'researcher', 'doctoral_student', 'partner', 'axe_admin', 'super_admin'];

            $mappedRolesResponse = Http::withToken($adminToken)->get("{$baseUrl}/admin/realms/{$realm}/users/{$keycloakId}/role-mappings/realm");
            if ($mappedRolesResponse->successful()) {
                $mappedRoles = $mappedRolesResponse->json();
                $rolesToDelete = array_filter($mappedRoles, function ($r) use ($rolesList) {
                    return in_array($r['name'], $rolesList);
                });

                if (!empty($rolesToDelete)) {
                    Http::withToken($adminToken)->delete("{$baseUrl}/admin/realms/{$realm}/users/{$keycloakId}/role-mappings/realm", array_values($rolesToDelete));
                }
            }

            $roleName = $user->role;
            $roleRepResponse = Http::withToken($adminToken)->get("{$baseUrl}/admin/realms/{$realm}/roles/{$roleName}");
            if ($roleRepResponse->successful()) {
                $roleRep = $roleRepResponse->json();
                Http::withToken($adminToken)->post("{$baseUrl}/admin/realms/{$realm}/users/{$keycloakId}/role-mappings/realm", [
                    $roleRep,
                ]);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Keycloak error during user role sync: ' . $e->getMessage());
        }

        return false;
    }
}
