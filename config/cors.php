<?php

return [
    'paths' => ['sanctum/csrf-cookie', 'login', 'logout'], // без 'api/*'
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'allowed_origins' => [],
    'allowed_origins_patterns' => [],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
