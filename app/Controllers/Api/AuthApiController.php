<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Repositories\UserRepository;
use App\Services\JwtService;
use Core\Database;
use Core\Request;
use Core\Response;
use Throwable;

final class AuthApiController
{
    public function login(Request $request, Response $response): void
    {
        $identifier = trim((string) $request->input('identifier', $request->input('email', '')));
        $password = (string) $request->input('password', '');

        if ($identifier === '' || $password === '') {
            $response->json([
                'success' => false,
                'message' => 'Identifier and password are required.',
                'errors' => [
                    'identifier' => $identifier === '' ? 'Identifier is required.' : null,
                    'password' => $password === '' ? 'Password is required.' : null,
                ],
            ], 422);
            return;
        }

        $userRepository = $this->userRepository();
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $userRepository->findByEmail($identifier)
            : ($userRepository->findByUsername($identifier) ?? $userRepository->findByEmail($identifier));

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $response->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
            return;
        }

        $jwtService = new JwtService();
        $token = $jwtService->issueToken($user);

        $response->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $jwtService->ttl(),
                'user' => $this->userData($user),
            ],
            'message' => 'Authenticated successfully.',
        ]);
    }

    public function register(Request $request, Response $response): void
    {
        $payload = [
            'username' => trim((string) $request->input('username', '')),
            'email' => trim((string) $request->input('email', '')),
            'password' => (string) $request->input('password', ''),
        ];

        $errors = $this->validateRegistration($payload);

        if ($errors !== []) {
            $response->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $errors,
            ], 422);
            return;
        }

        $userRepository = $this->userRepository();

        if ($userRepository->findByUsername($payload['username']) !== null) {
            $errors['username'] = 'Username is already in use.';
        }

        if ($userRepository->findByEmail($payload['email']) !== null) {
            $errors['email'] = 'Email is already in use.';
        }

        if ($errors !== []) {
            $response->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $errors,
            ], 422);
            return;
        }

        try {
            $userId = $userRepository->create([
                'username' => $payload['username'],
                'email' => $payload['email'],
                'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
                'role' => 'User',
            ]);
            $user = $userRepository->findById($userId);
        } catch (Throwable) {
            $response->json([
                'success' => false,
                'message' => 'Registration could not be completed.',
            ], 500);
            return;
        }

        if ($user === null) {
            $response->json([
                'success' => false,
                'message' => 'Registered user could not be loaded.',
            ], 500);
            return;
        }

        $jwtService = new JwtService();

        $response->json([
            'success' => true,
            'data' => [
                'token' => $jwtService->issueToken($user),
                'token_type' => 'Bearer',
                'expires_in' => $jwtService->ttl(),
                'user' => $this->userData($user),
            ],
            'message' => 'Registered successfully.',
        ], 201);
    }

    private function userRepository(): UserRepository
    {
        $database = new Database(require config_path('database.php'));

        return new UserRepository($database);
    }

    private function userData(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
        ];
    }

    private function validateRegistration(array $payload): array
    {
        $errors = [];

        if ($payload['username'] === '') {
            $errors['username'] = 'Username is required.';
        }

        if ($payload['email'] === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be a valid email address.';
        }

        if ($payload['password'] === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($payload['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        return $errors;
    }
}