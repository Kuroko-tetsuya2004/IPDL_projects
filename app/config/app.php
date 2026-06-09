<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nom de l'application
    |--------------------------------------------------------------------------
    |
    | Cette valeur est le nom de votre application. Elle sera utilisée
    | lorsque le framework doit afficher le nom de l'application dans
    | une notification ou un autre élément d'interface.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Environnement de l'application
    |--------------------------------------------------------------------------
    |
    | Cette valeur détermine l'« environnement » dans lequel s'exécute
    | votre application. Cela peut influencer la configuration de
    | différents services. Définissez-la dans votre fichier « .env ».
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Mode débogage de l'application
    |--------------------------------------------------------------------------
    |
    | Lorsque votre application est en mode débogage, des messages d'erreur
    | détaillés avec les traces de pile seront affichés pour chaque erreur.
    | Si désactivé, une page d'erreur générique simple est affichée.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de l'application
    |--------------------------------------------------------------------------
    |
    | Cette URL est utilisée par la console pour générer correctement les
    | URL lors de l'utilisation de l'outil en ligne de commande Artisan.
    | Définissez-la à la racine de votre application.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Fuseau horaire de l'application
    |--------------------------------------------------------------------------
    |
    | Vous pouvez spécifier ici le fuseau horaire par défaut de votre
    | application, utilisé par les fonctions PHP de date et d'heure.
    | Le fuseau est défini à « UTC » par défaut.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Configuration de la langue de l'application
    |--------------------------------------------------------------------------
    |
    | La langue de l'application détermine la locale par défaut utilisée
    | par les méthodes de traduction / localisation de Laravel.
    | Cette option peut être définie pour toute locale pour laquelle
    | vous prévoyez d'avoir des chaînes de traduction.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Clé de chiffrement
    |--------------------------------------------------------------------------
    |
    | Cette clé est utilisée par les services de chiffrement de Laravel
    | et doit être une chaîne aléatoire de 32 caractères pour garantir
    | la sécurité de toutes les valeurs chiffrées. Faites-le avant
    | de déployer l'application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pilote du mode maintenance
    |--------------------------------------------------------------------------
    |
    | Ces options de configuration déterminent le pilote utilisé pour
    | gérer le mode « maintenance » de Laravel. Le pilote « cache »
    | permet de contrôler le mode maintenance sur plusieurs machines.
    |
    | Pilotes supportés : "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
