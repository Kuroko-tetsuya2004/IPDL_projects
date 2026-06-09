<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paramètres d'authentification par défaut
    |--------------------------------------------------------------------------
    |
    | Cette option définit le « guard » d'authentification et le « broker »
    | de réinitialisation de mot de passe par défaut pour votre application.
    | Vous pouvez modifier ces valeurs selon vos besoins.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards d'authentification
    |--------------------------------------------------------------------------
    |
    | Définissez ici chaque guard d'authentification pour votre application.
    | Une configuration par défaut utilisant le stockage en session et le
    | fournisseur d'utilisateurs Eloquent est déjà définie.
    |
    | Tous les guards possèdent un fournisseur d'utilisateurs qui définit
    | comment les utilisateurs sont récupérés depuis la base de données
    | ou tout autre système de stockage utilisé par l'application.
    |
    | Supporté : "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fournisseurs d'utilisateurs
    |--------------------------------------------------------------------------
    |
    | Tous les guards possèdent un fournisseur d'utilisateurs qui définit
    | comment les utilisateurs sont récupérés depuis la base de données
    | ou tout autre système de stockage. Eloquent est utilisé par défaut.
    |
    | Si vous avez plusieurs tables ou modèles d'utilisateurs, vous pouvez
    | configurer plusieurs fournisseurs. Ces fournisseurs peuvent ensuite
    | être assignés à vos guards d'authentification supplémentaires.
    |
    | Supporté : "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Modules\User\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Réinitialisation des mots de passe
    |--------------------------------------------------------------------------
    |
    | Ces options de configuration définissent le comportement de la
    | fonctionnalité de réinitialisation de mot de passe de Laravel,
    | y compris la table de stockage des jetons et le fournisseur
    | d'utilisateurs invoqué pour récupérer les utilisateurs.
    |
    | Le temps d'expiration est le nombre de minutes pendant lesquelles
    | chaque jeton de réinitialisation sera considéré comme valide.
    |
    | Le paramètre de limitation est le nombre de secondes qu'un
    | utilisateur doit attendre avant de générer un nouveau jeton.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Délai de confirmation du mot de passe
    |--------------------------------------------------------------------------
    |
    | Définissez ici la durée en secondes avant qu'une fenêtre de
    | confirmation de mot de passe expire et que l'utilisateur soit
    | invité à ressaisir son mot de passe. Par défaut : 3 heures.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
