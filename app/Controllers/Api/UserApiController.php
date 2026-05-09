<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Interfaces\UserServiceInterface;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Core\Database;
use Core\Request;
use Core\Response;

final class UserApiController
{
    public function __construct(
        private readonly ?UserServiceInterface $userService = null
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));
        $filters = $this->filters($request);
        $sort = $this->sort($request);
        $service = $this->service();
        $total = $service->countFiltered($filters);
        $overview = $service->getOverview($page, $perPage, $filters, $sort['field'], $sort['direction']);

        $response->json([
            'success' => true,
            'data' => [
                'items' => (array) ($overview['users'] ?? []),
                'metrics' => (array) ($overview['metrics'] ?? []),
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => max(1, (int) ceil($total / $perPage)),
                ],
                'filters' => $filters,
                'sort' => $sort,
            ],
            'message' => 'Users retrieved successfully.',
        ]);
    }

    private function filters(Request $request): array
    {
        $role = trim((string) $request->query('role', ''));
        $allowedRoles = ['Admin', 'User'];

        if (!in_array($role, $allowedRoles, true)) {
            $role = '';
        }

        return [
            'username' => trim((string) $request->query('username', '')),
            'email' => trim((string) $request->query('email', '')),
            'role' => $role,
        ];
    }

    private function sort(Request $request): array
    {
        $field = trim((string) $request->query('sort', 'created_at'));
        $allowedFields = ['username', 'email', 'role', 'created_at'];

        if (!in_array($field, $allowedFields, true)) {
            $field = 'created_at';
        }

        $direction = strtolower((string) $request->query('direction', 'desc'));

        return [
            'field' => $field,
            'direction' => $direction === 'asc' ? 'asc' : 'desc',
        ];
    }

    private function service(): UserServiceInterface
    {
        if ($this->userService instanceof UserServiceInterface) {
            return $this->userService;
        }

        $database = new Database(require config_path('database.php'));

        return new UserService(new UserRepository($database));
    }
}