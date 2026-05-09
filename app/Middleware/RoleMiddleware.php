<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Middleware\MiddlewareInterface;
use Core\Request;
use Core\Response;

final class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $role
    ) {
    }

    public function handle(Request $request, Response $response): bool
    {
        if ((bool) $request->session('authenticated', false) !== true) {
            $request->setSessionValue('flash', 'Please log in to continue.');
            $response->redirect('/login');
            return false;
        }

        if ($request->session('role') === $this->role) {
            return true;
        }

        $response->json([
            'success' => false,
            'message' => 'Forbidden',
        ], 403);

        return false;
    }
}