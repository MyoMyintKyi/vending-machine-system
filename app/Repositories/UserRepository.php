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
}