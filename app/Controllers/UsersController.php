<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Interfaces\UserServiceInterface;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Core\Database;
use Core\Request;
use Core\Response;

final class UsersController
{
    public function __construct(
        private readonly ?UserServiceInterface $userService = null
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $filters = $this->filters($request);
        $sort = $this->sort($request);
        $totalUsers = $this->service()->countFiltered($filters);
        $totalPages = max(1, (int) ceil($totalUsers / $perPage));
        $page = min($page, $totalPages);
        $overview = $this->service()->getOverview($page, $perPage, $filters, $sort['field'], $sort['direction']);

        $response->view('users/index', [
            'title' => 'Users Module',
            'flash' => (string) $request->pullSessionValue('flash', ''),
            'role' => (string) $request->session('role', ''),
            'metrics' => (array) ($overview['metrics'] ?? []),
            'users' => (array) ($overview['users'] ?? []),
            'filters' => $filters,
            'sort' => $sort['field'],
            'direction' => $sort['direction'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'hasPreviousPage' => $page > 1,
            'hasNextPage' => $page < $totalPages,
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