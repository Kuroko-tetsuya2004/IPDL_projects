<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Connexion de file d'attente par défaut
    |--------------------------------------------------------------------------
    |
    | Le système de files d'attente de Laravel prend en charge plusieurs
    | backends via une API unifiée, vous donnant un accès pratique à
    | chaque backend avec une syntaxe identique.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Connexions des files d'attente
    |--------------------------------------------------------------------------
    |
    | Configurez ici les options de connexion pour chaque backend de file
    | d'attente utilisé par votre application. Un exemple de configuration
    | est fourni pour chaque backend supporté par Laravel.
    |
    | Pilotes : "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Traitement par lots des tâches
    |--------------------------------------------------------------------------
    |
    | Les options suivantes configurent la base de données et la table
    | qui stockent les informations de traitement par lots des tâches.
    | Ces options peuvent être mises à jour vers n'importe quelle
    | connexion et table définie par votre application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tâches de file d'attente échouées
    |--------------------------------------------------------------------------
    |
    | Ces options configurent le comportement de journalisation des tâches
    | échouées afin de contrôler comment et où elles sont stockées.
    | Laravel prend en charge le stockage dans un fichier ou en base.
    |
    | Pilotes supportés : "database-uuids", "dynamodb", "file", "null"
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'failed_jobs',
    ],

];
