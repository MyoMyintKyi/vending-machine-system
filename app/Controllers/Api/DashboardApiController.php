<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Core\Request;
use Core\Response;

final class DashboardApiController
{
    public function index(Request $request, Response $response): void
    {
        $user = (array) $request->attribute('auth.user', []);

        $response->json([
            'success' => true,
            'data' => [
                'username' => (string) ($user['username'] ?? ''),
                'email' => (string) ($user['email'] ?? ''),
                'role' => (string) ($user['role'] ?? ''),
            ],
            'message' => 'Dashboard retrieved successfully.',
        ]);
    }
}