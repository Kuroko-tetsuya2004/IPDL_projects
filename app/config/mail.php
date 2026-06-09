<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Expéditeur de courrier par défaut
    |--------------------------------------------------------------------------
    |
    | Cette option contrôle l'expéditeur par défaut utilisé pour envoyer
    | tous les e-mails, sauf si un autre est explicitement spécifié.
    | Tous les expéditeurs supplémentaires peuvent être configurés dans
    | le tableau « mailers ». Des exemples de chaque type sont fournis.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Configurations des expéditeurs
    |--------------------------------------------------------------------------
    |
    | Configurez ici tous les expéditeurs utilisés par votre application
    | ainsi que leurs paramètres respectifs. Plusieurs exemples sont
    | configurés et vous pouvez en ajouter selon vos besoins.
    |
    | Laravel prend en charge une variété de pilotes de « transport »
    | pour l'envoi d'e-mails. Vous pouvez spécifier celui que vous
    | utilisez pour vos expéditeurs ci-dessous.
    |
    | Supportés : "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |             "postmark", "resend", "log", "array",
    |             "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Adresse d'expédition globale
    |--------------------------------------------------------------------------
    |
    | Vous pouvez souhaiter que tous les e-mails envoyés par votre
    | application proviennent de la même adresse. Spécifiez ici un nom
    | et une adresse utilisés globalement pour tous les e-mails envoyés.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

];
