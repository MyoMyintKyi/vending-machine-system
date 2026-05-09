<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use Core\Request;
use DomainException;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function attempt(string $identifier, string $password, Request $request): bool
    {
        $user = $this->findByIdentifier($identifier);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $this->storeAuthenticatedUser($request, $user);

        return true;
    }

    public function register(array $data): array
    {
        if ($this->userRepository->findByUsername($data['username']) !== null) {
            throw new DomainException('That username is already in use.');
        }

        if ($this->userRepository->findByEmail($data['email']) !== null) {
            throw new DomainException('That email address is already in use.');
        }

        $userId = $this->userRepository->create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'User',
        ]);

        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new DomainException('User registration could not be completed.');
        }

        return $user;
    }

    public function logout(Request $request): void
    {
        $request->invalidateSession();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function user(Request $request): ?array
    {
        $userId = (int) $request->session('user_id', 0);

        if ($userId <= 0) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    private function findByIdentifier(string $identifier): ?array
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->userRepository->findByEmail($identifier);
        }

        return $this->userRepository->findByUsername($identifier)
            ?? $this->userRepository->findByEmail($identifier);
    }

    private function storeAuthenticatedUser(Request $request, array $user): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $request->setSessionValue('user_id', (int) $user['id']);
        $request->setSessionValue('username', $user['username']);
        $request->setSessionValue('email', $user['email']);
        $request->setSessionValue('role', $user['role']);
        $request->setSessionValue('authenticated', true);
    }
}