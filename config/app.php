<?php

require_once __DIR__ . '/env.php';

loadEnvFile(__DIR__ . '/../.env');

$config = [
    'app' => [
        'name' => envValue('APP_NAME', 'Presentation Scheduler'),
        'env' => envValue('APP_ENV', 'local'),
        'base_url' => rtrim((string) envValue('APP_BASE_URL', ''), '/'),
        'timezone' => envValue('APP_TIMEZONE', 'Europe/Sofia'),
    ],
    'db' => [
        'host' => envValue('DB_HOST', 'localhost'),
        'port' => (int) envValue('DB_PORT', 3306),
        'database' => envValue('DB_DATABASE', 'presentation_scheduler'),
        'username' => envValue('DB_USERNAME', 'root'),
        'password' => envValue('DB_PASSWORD', ''),
        'charset' => envValue('DB_CHARSET', 'utf8mb4'),
    ],
    'aws' => [
        'region' => envValue('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => envValue('AWS_BUCKET', ''),
        'access_key_id' => envValue('AWS_ACCESS_KEY_ID', ''),
        'secret_access_key' => envValue('AWS_SECRET_ACCESS_KEY', ''),
        'session_token' => envValue('AWS_SESSION_TOKEN', ''),
    ],
];

date_default_timezone_set($config['app']['timezone']);

return $config;
