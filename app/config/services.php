<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Services tiers
    |--------------------------------------------------------------------------
    |
    | Ce fichier sert à stocker les identifiants des services tiers tels
    | que Mailgun, Postmark, AWS et autres. Il fournit un emplacement
    | conventionnel pour que les packages puissent localiser les
    | différentes informations d'identification des services.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Keycloak SSO (Socialite)
    |--------------------------------------------------------------------------
    */
    'keycloak' => [
        'client_id'     => env('KEYCLOAK_CLIENT_ID', 'ummisco-portail'),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET', ''),
        'redirect'      => env('KEYCLOAK_REDIRECT_URI', '/auth/callback'),
        'base_url'      => env('KEYCLOAK_BASE_URL', 'http://keycloak:8080'),
        'realms'        => env('KEYCLOAK_REALM', 'ummisco'),
    ],

];
