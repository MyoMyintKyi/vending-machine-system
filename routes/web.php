<?php

declare(strict_types=1);

use App\Controllers\AuthController as WebAuthController;
use App\Controllers\DashboardController;
use App\Controllers\TestController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

$router->get('/', static function ($request, $response): void {
    $response->json([
        'message' => 'Vending Machine web bootstrap is ready.',
    ]);
});

$router->get('/login', [WebAuthController::class, 'loginForm']);
$router->post('/login', [WebAuthController::class, 'login']);
$router->get('/register', [WebAuthController::class, 'registerForm']);
$router->post('/register', [WebAuthController::class, 'register']);
$router->post('/logout', [WebAuthController::class, 'logout'], [AuthMiddleware::class]);

$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/admin', [DashboardController::class, 'admin'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);

$router->get('/test', [TestController::class, 'index']);
$router->get('/test/protected', [TestController::class, 'authenticated'], [AuthMiddleware::class]);
$router->get('/test/admin', [TestController::class, 'adminOnly'], [AuthMiddleware::class, [RoleMiddleware::class, 'Admin']]);