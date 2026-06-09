<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disque de fichiers par défaut
    |--------------------------------------------------------------------------
    |
    | Spécifiez ici le disque de fichiers par défaut utilisé par le framework.
    | Le disque « local » ainsi que divers disques cloud sont disponibles
    | pour le stockage de fichiers de votre application.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Disques de fichiers
    |--------------------------------------------------------------------------
    |
    | Configurez ci-dessous autant de disques que nécessaire. Vous pouvez
    | même configurer plusieurs disques pour le même pilote. Des exemples
    | pour les pilotes de stockage les plus courants sont fournis.
    |
    | Pilotes supportés : "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        'minio' => [
            'driver' => 's3',
            'key' => env('MINIO_KEY', 'minio_admin'),
            'secret' => env('MINIO_SECRET', 'minio_secret_2024'),
            'region' => env('MINIO_REGION', 'us-east-1'),
            'bucket' => env('MINIO_BUCKET_PUBLIC', 'ummisco-public'),
            'endpoint' => env('MINIO_ENDPOINT', 'http://minio:9000'),
            'use_path_style_endpoint' => env('MINIO_USE_PATH_STYLE', true),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Liens symboliques
    |--------------------------------------------------------------------------
    |
    | Configurez ici les liens symboliques qui seront créés lors de
    | l'exécution de la commande Artisan `storage:link`. Les clés du
    | tableau sont les emplacements des liens et les valeurs leurs cibles.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
