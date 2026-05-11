<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $basePath = __DIR__ . '/..';

        return $path === '' ? $basePath : $basePath . '/' . ltrim($path, '/');
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path === '' ? '' : '/' . ltrim($path, '/')));
    }
}

if (!function_exists('env')) {
    function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false || $value === null ? $default : (string) $value;
    }
}

if (!function_exists('format_view_number')) {
    function format_view_number(int|float|string|null $value): string
    {
        return \App\Support\ViewNumberFormatter::format($value);
    }
}

if (!function_exists('product_slug')) {
    function product_slug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'product';
    }
}

if (!function_exists('product_purchase_path')) {
    function product_purchase_path(int $id, string $name): string
    {
        return '/products/' . $id . '-' . product_slug($name) . '/purchase';
    }
}