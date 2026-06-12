<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Pilote de session par défaut
    |--------------------------------------------------------------------------
    |
    | Cette option détermine le pilote de session par défaut utilisé pour
    | les requêtes entrantes. Laravel prend en charge plusieurs options
    | de stockage pour persister les données de session. Le stockage en
    | base de données est un excellent choix par défaut.
    |
    | Supporté : "file", "cookie", "database", "apc",
    |            "memcached", "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Durée de vie de la session
    |--------------------------------------------------------------------------
    |
    | Spécifiez ici le nombre de minutes pendant lesquelles la session
    | peut rester inactive avant d'expirer. Si vous souhaitez qu'elle
    | expire immédiatement à la fermeture du navigateur, activez
    | l'option expire_on_close ci-dessous.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Chiffrement de la session
    |--------------------------------------------------------------------------
    |
    | Cette option permet de spécifier que toutes les données de session
    | doivent être chiffrées avant d'être stockées. Le chiffrement est
    | effectué automatiquement par Laravel et vous pouvez utiliser la
    | session normalement.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Emplacement des fichiers de session
    |--------------------------------------------------------------------------
    |
    | Lors de l'utilisation du pilote de session « file », les fichiers
    | de session sont placés sur le disque. L'emplacement de stockage
    | par défaut est défini ici ; vous pouvez le modifier si nécessaire.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Connexion à la base de données de session
    |--------------------------------------------------------------------------
    |
    | Lors de l'utilisation des pilotes « database » ou « redis », vous
    | pouvez spécifier une connexion à utiliser pour gérer les sessions.
    | Celle-ci doit correspondre à une connexion dans votre configuration.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Table de session en base de données
    |--------------------------------------------------------------------------
    |
    | Lors de l'utilisation du pilote « database », vous pouvez spécifier
    | la table utilisée pour stocker les sessions. Une valeur par défaut
    | sensée est définie ; vous pouvez la modifier si nécessaire.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Magasin de cache pour les sessions
    |--------------------------------------------------------------------------
    |
    | Lors de l'utilisation d'un backend de session basé sur le cache,
    | vous pouvez définir le magasin de cache qui doit stocker les
    | données de session entre les requêtes. Celui-ci doit correspondre
    | à l'un de vos magasins de cache définis.
    |
    | Affecte : "apc", "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Loterie de nettoyage des sessions
    |--------------------------------------------------------------------------
    |
    | Certains pilotes de session doivent nettoyer manuellement leur
    | emplacement de stockage pour supprimer les anciennes sessions.
    | Voici les probabilités que cela se produise sur une requête
    | donnée. Par défaut : 2 chances sur 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nom du cookie de session
    |--------------------------------------------------------------------------
    |
    | Modifiez ici le nom du cookie de session créé par le framework.
    | En général, vous ne devriez pas avoir besoin de changer cette
    | valeur car cela n'apporte pas d'amélioration significative
    | de la sécurité.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Chemin du cookie de session
    |--------------------------------------------------------------------------
    |
    | Le chemin du cookie de session détermine le chemin pour lequel le
    | cookie sera considéré comme disponible. En général, il s'agit du
    | chemin racine de votre application.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Domaine du cookie de session
    |--------------------------------------------------------------------------
    |
    | Cette valeur détermine le domaine et les sous-domaines pour lesquels
    | le cookie de session est disponible. Par défaut, le cookie sera
    | disponible pour le domaine racine et tous ses sous-domaines.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Cookies HTTPS uniquement
    |--------------------------------------------------------------------------
    |
    | En activant cette option, les cookies de session ne seront renvoyés
    | au serveur que si le navigateur dispose d'une connexion HTTPS.
    | Cela empêche l'envoi du cookie en l'absence de connexion sécurisée.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | Accès HTTP uniquement
    |--------------------------------------------------------------------------
    |
    | En activant cette option, JavaScript ne pourra pas accéder à la
    | valeur du cookie ; celui-ci ne sera accessible que via le protocole
    | HTTP. Il est déconseillé de désactiver cette option.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Cookies Same-Site
    |--------------------------------------------------------------------------
    |
    | Cette option détermine le comportement de vos cookies lors de
    | requêtes inter-sites et peut être utilisée pour atténuer les
    | attaques CSRF. Par défaut, la valeur est « lax » pour permettre
    | les requêtes inter-sites sécurisées.
    |
    | Supporté : "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'strict'),

    /*
    |--------------------------------------------------------------------------
    | Cookies partitionnés
    |--------------------------------------------------------------------------
    |
    | En activant cette option, le cookie sera lié au site de premier
    | niveau dans un contexte inter-sites. Les cookies partitionnés sont
    | acceptés par le navigateur lorsqu'ils sont marqués « secure » et
    | que l'attribut Same-Site est défini à « none ».
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
