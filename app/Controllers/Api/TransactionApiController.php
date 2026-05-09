<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Repositories\TransactionRepository;
use Core\Database;
use Core\Request;
use Core\Response;

final class TransactionApiController
{
    public function index(Request $request, Response $response): void
    {
        $response->json([
            'success' => true,
            'data' => $this->repository()->listAll(),
            'message' => 'Transactions retrieved successfully.',
        ]);
    }

    public function userTransactions(Request $request, Response $response): void
    {
        $requestedUserId = (int) $request->route('id', 0);
        $authenticatedUserId = (int) $request->attribute('auth.user_id', 0);
        $role = (string) $request->attribute('auth.role', '');

        if ($requestedUserId <= 0) {
            $response->json([
                'success' => false,
                'message' => 'User id is invalid.',
            ], 422);
            return;
        }

        if ($role !== 'Admin' && $requestedUserId !== $authenticatedUserId) {
            $response->json([
                'success' => false,
                'message' => 'You do not have permission to view these transactions.',
            ], 403);
            return;
        }

        $response->json([
            'success' => true,
            'data' => $this->repository()->listByUser($requestedUserId),
            'message' => 'User transactions retrieved successfully.',
        ]);
    }

    private function repository(): TransactionRepository
    {
        $database = new Database(require config_path('database.php'));

        return new TransactionRepository($database);
    }
}