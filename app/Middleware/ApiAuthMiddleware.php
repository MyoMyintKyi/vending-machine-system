<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Repositories\UserRepository;
use App\Services\JwtService;
use Core\Database;
use Core\Request;
use Core\Response;
use Throwable;

final class ApiAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ?string $requiredRole = null
    ) {
    }

    public function handle(Request $request, Response $response): bool
    {
        $authorization = (string) $request->header('Authorization', '');

        if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            $this->unauthorized($response, 'Missing or invalid bearer token.');
            return false;
        }

        try {
            $jwtService = new JwtService();
            $claims = $jwtService->decodeToken(trim($matches[1]));
            $userId = (int) ($claims['sub'] ?? 0);

            if ($userId <= 0) {
                $this->unauthorized($response, 'Invalid token subject.');
                return false;
            }

            $database = new Database(require config_path('database.php'));
            $userRepository = new UserRepository($database);
            $user = $userRepository->findById($userId);

            if ($user === null) {
                $this->unauthorized($response, 'Authenticated user no longer exists.');
                return false;
            }
        } catch (Throwable) {
            $this->unauthorized($response, 'Token is invalid or expired.');
            return false;
        }

        if ($this->requiredRole !== null && $user['role'] !== $this->requiredRole) {
            $response->json([
                'success' => false,
                'message' => 'You do not have permission to access this resource.',
            ], 403);
            return false;
        }

        $request->setAttribute('auth.user', $user);
        $request->setAttribute('auth.user_id', (int) $user['id']);
        $request->setAttribute('auth.role', (string) $user['role']);
        $request->setAttribute('auth.claims', $claims);

        return true;
    }

    private function unauthorized(Response $response, string $message): void
    {
        $response->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }
}