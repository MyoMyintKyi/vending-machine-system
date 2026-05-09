<?php

declare(strict_types=1);

use App\Controllers\AuthController as WebAuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProductsController;
use App\Controllers\TransactionController;
use App\Controllers\UsersController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

$router->get('/', static function ($request, $response): void {
    $response->json([
        'message' => 'Vending Machine web bootstrap is ready.',
    ]);
});

$router->get('/login', [WebAuthController::class, 'loginForm']);
$router->post('/login', [WebAuthController::class, 'login']);
$router->post('/logout', [WebAuthController::class, 'logout'], [AuthMiddleware::class]);

$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/users', [UsersController::class, 'index'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);
$router->get('/transactions', [TransactionController::class, 'index'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);

$router->get('/products', [ProductsController::class, 'index'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);
$router->get('/products/create', [ProductsController::class, 'create'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);
$router->post('/products', [ProductsController::class, 'store'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);
$router->get('/products/{id}', [ProductsController::class, 'show'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);
$router->get('/products/{id}/edit', [ProductsController::class, 'edit'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);
$router->post('/products/{id}/update', [ProductsController::class, 'update'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);
$router->post('/products/{id}/delete', [ProductsController::class, 'destroy'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);

$router->get('/products/{id}/purchase', [ProductsController::class, 'purchaseForm'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);
$router->post('/products/{id}/purchase', [ProductsController::class, 'purchase'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);