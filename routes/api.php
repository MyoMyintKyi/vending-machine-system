<?php

declare(strict_types=1);

$router->get('/api/health', static function ($request, $response): void {
    $response->json([
        'status' => 'ok',
    ]);
});