<?php
declare(strict_types=1);

use App\Core\Env;

return [
    'app' => [
        'url' => Env::get('APP_URL', 'http://localhost:8000'),
    ],
    'api' => [
        'public_url' => Env::get('API_PUBLIC_URL', 'http://localhost:8001'),
        'internal_url' => Env::get('API_INTERNAL_URL', 'http://api:8001'),
        'email' => Env::get('API_EMAIL', 'admin@finisher.test'),
        'password' => Env::get('API_PASSWORD', 'Password123!'),
    ],
    'db' => [
        'dsn' => Env::get('DB_DSN', ''),
        'user' => Env::get('DB_USER', ''),
        'pass' => Env::get('DB_PASS', ''),
    ],
    'mail' => [
        'driver' => Env::get('MAIL_DRIVER', 'smtp'),
        'host' => Env::get('MAIL_HOST', ''),
        'port' => Env::get('MAIL_PORT', ''),
        'user' => Env::get('MAIL_USER', ''),
        'pass' => Env::get('MAIL_PASS', ''),
        'from' => Env::get('MAIL_FROM', 'finisher@local.test'),
        'from_name' => Env::get('MAIL_FROM_NAME', 'Finisher'),
    ],
];
