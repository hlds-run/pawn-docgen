<?php

declare(strict_types=1);

return [
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => false,
    ],
    'database' => [
        'driver' => 'pdo_mysql',
        'host' => getenv('MYSQL_HOST') ?: 'mysql_db',
        'dbname' => getenv('MYSQL_DATABASE') ?: 'pawndoc',
        'user' => getenv('MYSQL_USER') ?: 'root',
        'password' => getenv('MYSQL_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => getenv('APP_NAME') ?: 'AMXModX',
        'base_url' => getenv('BASE_URL') ?: '/',
        'og_hmac_secret' => getenv('OG_HMAC_SECRET') ?: 'default_secret',
        'og_hmac_symbols' => (int)(getenv('CHECK_HMAC_SYMBOLS') ?: 8),
    ],
];
