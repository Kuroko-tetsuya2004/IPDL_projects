<?php

/**
 * Configuration de la base de données — Portail UMMISCO
 *
 * Mappé sur le schéma ummisco_database.sql (PostgreSQL 16)
 * Tables : 35 | ENUMs : 9 | Extensions : uuid-ossp, pg_trgm, unaccent, btree_gin
 */

return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        // ── PostgreSQL 16 — Base principale ─────────────────────────────────
        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', 'postgres'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE', 'ummisco_app'),
            'username'       => env('DB_USERNAME', 'ummisco_user'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => 'public',
            'sslmode'        => 'prefer',
            // Options spécifiques au schéma UMMISCO
            'options'        => [
                // Activer les ENUMs PostgreSQL natifs
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],

        // ── PostgreSQL — Base de test ────────────────────────────────────────
        'pgsql_test' => [
            'driver'         => 'pgsql',
            'host'           => env('DB_HOST', 'postgres'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_TEST_DATABASE', 'ummisco_test'),
            'username'       => env('DB_USERNAME', 'ummisco_user'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'search_path'    => 'public',
            'sslmode'        => 'prefer',
        ],

        // ── SQLite — Tests unitaires rapides ─────────────────────────────────
        'sqlite' => [
            'driver'                  => 'sqlite',
            'url'                     => env('DATABASE_URL'),
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

    ],

    // ── Migrations ───────────────────────────────────────────────────────────
    'migrations' => [
        'table'       => 'migrations',
        'update_date_on_publish' => true,
    ],

    // ── Redis ────────────────────────────────────────────────────────────────
    'redis' => [
        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster'    => env('REDIS_CLUSTER', 'redis'),
            'prefix'     => env('REDIS_PREFIX', 'ummisco_'),
        ],

        // Connexion par défaut
        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'redis'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        // Cache dédié (db 1)
        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'redis'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

        // Sessions dédiées (db 2)
        'sessions' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'redis'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
        ],

        // Queues dédiées (db 3)
        'queues' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'redis'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '3'),
        ],
    ],

];
