<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/autoload.php';

$autoloadFile = base_path('vendor/autoload.php');

if (file_exists($autoloadFile)) {
    require_once $autoloadFile;
}

$envFile = base_path('.env');
$exampleEnvFile = base_path('.env.example');
$activeEnvFile = file_exists($envFile) ? $envFile : $exampleEnvFile;

if (file_exists($activeEnvFile)) {
    $lines = file($activeEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $trimmed, 2);
        $key = trim($key);
        $value = trim($value);

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(env('SESSION_NAME', 'vending_machine_session'));
    session_start();
}

$router = new Core\Router();
$request = Core\Request::capture();
$response = new Core\Response();

require base_path('routes/web.php');
require base_path('routes/api.php');

return [$router, $request, $response];