<?php

// This file is deprecated - using src/settings.php instead
// Kept for compatibility with generate/update.php script

$Config = Array(
    'ConnectionString' => getenv('MYSQL_CONNECTION_STRING'),
    'User'             => getenv('MYSQL_USER'),
    'Password'         => getenv('MYSQL_PASSWORD'),
);

$Columns = Array(
    'Functions' => 'pawnfunctions',
    'Constants' => 'pawnconstants',
    'Files'     => 'pawnfiles'
);

$BaseURL = getenv('BASE_URL') ?: '/';
$Project = getenv('APP_NAME') ?: 'AMXModX';

$Database = @new PDO(
    $Config['ConnectionString'],
    $Config['User'],
    $Config['Password'],
    Array(
        PDO::ATTR_TIMEOUT            => 1,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    )
);

unset($Config);
