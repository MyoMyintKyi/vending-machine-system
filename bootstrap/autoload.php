<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\' => base_path('app'),
        'Core\\' => base_path('core'),
        'Config\\' => base_path('config'),
        'Tests\\' => base_path('tests'),
    ];

    foreach ($prefixes as $prefix => $baseDirectory) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDirectory . '/' . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }

        return;
    }
});