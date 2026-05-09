<?php

declare(strict_types=1);

use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\ProductApiController;
use App\Controllers\Api\TransactionApiController;
use App\Middleware\ApiAuthMiddleware;

$router->get('/api/health', static function ($request, $response): void {
    $response->json([
        'success' => true,
        'data' => [
            'status' => 'ok',
        ],
        'message' => 'API is healthy.',
    ]);
});

$router->post('/api/auth/login', [AuthApiController::class, 'login']);
$router->post('/api/auth/register', [AuthApiController::class, 'register']);

$router->get('/api/products', [ProductApiController::class, 'index'],[[ApiAuthMiddleware::class, 'Admin']]);
$router->get('/api/products/{id}', [ProductApiController::class, 'show'], [[ApiAuthMiddleware::class, 'Admin']]);
$router->post('/api/products', [ProductApiController::class, 'store'], [[ApiAuthMiddleware::class, 'Admin']]);
$router->put('/api/products/{id}', [ProductApiController::class, 'update'], [[ApiAuthMiddleware::class, 'Admin']]);
$router->delete('/api/products/{id}', [ProductApiController::class, 'destroy'], [[ApiAuthMiddleware::class, 'Admin']]);

$router->post('/api/products/{id}/purchase', [ProductApiController::class, 'purchase'], [[ApiAuthMiddleware::class, 'Admin']]);

$router->get('/api/transactions', [TransactionApiController::class, 'index'], [[ApiAuthMiddleware::class, 'Admin']]);