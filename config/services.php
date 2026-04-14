<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
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
    'stripe' => [
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CardNet / Ztrans (República Dominicana)
    |--------------------------------------------------------------------------
    | CARDNET_ENV=QA          → usa labservicios.cardnet.com.do (pruebas)
    | CARDNET_ENV=production  → usa ecommerce.cardnet.com.do
    |
    | Datos de QA por defecto (del documento de integración):
    |   merchant_id = 349041263
    |   terminal_id = 77777777
    |   currency    = 214 (DOP)
    */
    'cardnet' => [
        'env'         => env('CARDNET_ENV', 'QA'),
        'merchant_id' => env('CARDNET_MERCHANT_ID', ''),
        'terminal_id' => env('CARDNET_TERMINAL_ID', ''),
        'token'       => env('CARDNET_TOKEN', ''),
        'environment' => env('CARDNET_ENVIRONMENT', 'ECommerce'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver de pago activo
    |--------------------------------------------------------------------------
    | Cambia PAYMENT_DRIVER en .env para alternar entre proveedores:
    |   PAYMENT_DRIVER=cardnet   → CardNet/Ztrans
    |   PAYMENT_DRIVER=stripe    → Stripe
    */
    'payment' => [
        'driver' => env('PAYMENT_DRIVER', 'cardnet'),
    ],

    // ── OAuth Social Login ──────────────────────────────────────────────────
    // Las credenciales se pueden sobreescribir desde la tabla oauth_providers
    // en la BD. El .env actúa como fallback.

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', ''),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI', ''),
    ],

    'instagram' => [
        'client_id'     => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect'      => env('INSTAGRAM_REDIRECT_URI', ''),
    ],

];
