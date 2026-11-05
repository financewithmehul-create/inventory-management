<?php

/*
 * The app and its API are used from the same site only. No other website may call the API from a browser.
 */
return [
    'paths'                    => ['api/*', 'admin/api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
    'allowed_origins'          => array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))),
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => false,
];
