<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/helpers.php';
require_once __DIR__ . '/../bootstrap/autoload.php';

$vendorAutoload = base_path('vendor/autoload.php');
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

$envFile = base_path('.env.testing');
if (!file_exists($envFile)) {
    throw new RuntimeException('.env.testing is required for integration tests.');
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

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
    putenv($key . '=' . $value);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(env('SESSION_NAME', 'vending_machine_test_session'));
    session_start();
}