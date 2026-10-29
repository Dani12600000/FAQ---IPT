<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // A API do FAQ é só de leitura e pública.
    'paths' => ['api/*'],

    'allowed_methods' => ['GET'],

    // Sites que podem chamar a API a partir do browser (por exemplo, o widget no site do IPT),
    // separados por vírgulas: CORS_ALLOWED_ORIGINS=https://www.ipt.pt,https://portal.ipt.pt
    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '*')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
