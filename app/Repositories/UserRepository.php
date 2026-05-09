<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

final class UserRepository
{
    public function __construct(
        private readonly Database $database
    ) {
    }

    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)',
            [
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => $data['password_hash'],
                'role' => $data['role'] ?? 'User',
            ]
        );

        return (int) $this->database->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->database->query(
            'SELECT id, username, email, password_hash, role, created_at, updated_at FROM users WHERE id = :id LIMIT 1',
            ['id' => $id]
        );

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->database->query(
            'SELECT id, username, email, password_hash, role, created_at, updated_at FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->database->query(
            'SELECT id, username, email, password_hash, role, created_at, updated_at FROM users WHERE username = :username LIMIT 1',
            ['username' => $username]
        );

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function countFiltered(array $filters = []): int
    {
        $bindings = [];
        $whereClause = $this->filterClause($filters, $bindings);
        $statement = $this->database->query(
            'SELECT COUNT(*) AS total FROM users' . $whereClause,
            $bindings
        );
        $result = $statement->fetch();

        return (int) ($result['total'] ?? 0);
    }

    public function summarizeFiltered(array $filters = []): array
    {
        $bindings = [];
        $whereClause = $this->filterClause($filters, $bindings);
        $statement = $this->database->query(
            "SELECT COUNT(*) AS total_users, SUM(CASE WHEN role = 'Admin' THEN 1 ELSE 0 END) AS total_admins, SUM(CASE WHEN role = 'User' THEN 1 ELSE 0 END) AS total_standard_users, MAX(created_at) AS latest_joined_at FROM users" . $whereClause,
            $bindings
        );
        $result = $statement->fetch();

        return $result === false ? [] : $result;
    }

    public function listFiltered(array $filters = [], int $page = 1, int $perPage = 10, string $sortBy = 'created_at', string $direction = 'desc'): array
    {
        $allowedSorts = [
            'username' => 'username',
            'email' => 'email',
            'role' => 'role',
            'created_at' => 'created_at',
        ];

        $sortColumn = $allowedSorts[$sortBy] ?? 'created_at';
        $sortDirection = strtolower($direction) === 'asc' ? 'ASC' : 'DESC';
        $safePerPage = max(1, $perPage);
        $offset = (max(1, $page) - 1) * $safePerPage;
        $bindings = [];
        $whereClause = $this->filterClause($filters, $bindings);

        return $this->database->query(
            sprintf(
                'SELECT id, username, email, role, created_at, updated_at FROM users%s ORDER BY %s %s LIMIT :limit OFFSET :offset',
                $whereClause,
                $sortColumn,
                $sortDirection
            ),
            array_merge($bindings, [
                'limit' => $safePerPage,
                'offset' => $offset,
            ])
        )->fetchAll();
    }

    private function filterClause(array $filters, array &$bindings): string
    {
        $conditions = [];

        $username = trim((string) ($filters['username'] ?? ''));
        if ($username !== '') {
            $conditions[] = 'username LIKE :username';
            $bindings['username'] = '%' . $username . '%';
        }

        $email = trim((string) ($filters['email'] ?? ''));
        if ($email !== '') {
            $conditions[] = 'email LIKE :email';
            $bindings['email'] = '%' . $email . '%';
        }

        $role = trim((string) ($filters['role'] ?? ''));
        if ($role !== '') {
            $conditions[] = 'role = :role';
            $bindings['role'] = $role;
        }

        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }
}