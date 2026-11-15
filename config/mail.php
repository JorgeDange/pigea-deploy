<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
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
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        /*
         | Mailer transacional do Diretório (registo de empresa/profissional,
         | confirmação e decisão) e do formulário de contacto: envia como
         | `geral@pigea.ao`, separado da stream da newsletter (que fica no
         | mailer por omissão, From `newsletter@pigea.ao`). O relay cPanel
         | recusa remetentes que não sejam a conta autenticada — por isso
         | `geral@pigea.ao` precisa das suas próprias credenciais.
         */
        'geral' => [
            'transport' => env('MAIL_GERAL_TRANSPORT', 'smtp'),
            'scheme' => env('MAIL_GERAL_SCHEME', env('MAIL_SCHEME')),
            'url' => env('MAIL_GERAL_URL'),
            'host' => env('MAIL_GERAL_HOST', env('MAIL_HOST', 'mail.pigea.ao')),
            'port' => env('MAIL_GERAL_PORT', env('MAIL_PORT', 465)),
            'username' => env('MAIL_GERAL_USERNAME', 'geral@pigea.ao'),
            'password' => env('MAIL_GERAL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_GERAL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
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
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

    /*
     | Remetente usado pelos mailables transacionais (registo/decisão do
     | Diretório e formulário de contacto). Ignorado se a variável não
     | existir — fica o mesmo nome amigável do PIGEA.
     */
    'geral_from' => [
        'address' => env('MAIL_GERAL_FROM_ADDRESS', 'geral@pigea.ao'),
        'name' => env('MAIL_GERAL_FROM_NAME', 'PIGEA'),
    ],

];
