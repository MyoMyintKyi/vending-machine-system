<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Interfaces\AuthServiceInterface;
use Core\Database;
use Core\Request;
use Core\Response;

final class AuthController
{
    public function __construct(
        private readonly ?AuthServiceInterface $authService = null
    ) {
    }

    public function loginForm(Request $request, Response $response): void
    {
        if ((bool) $request->session('authenticated', false) === true) {
            $response->redirect('/dashboard');
            return;
        }

        $response->view('auth/login', $this->consumeFormState($request), 'layouts/guest');
    }

    public function login(Request $request, Response $response): void
    {
        $identifier = trim((string) $request->input('identifier', ''));
        $password = (string) $request->input('password', '');

        $errors = [];

        if ($identifier === '') {
            $errors['identifier'] = 'Username or email is required.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if ($errors !== []) {
            $this->redirectWithFormState($request, $response, '/login', $errors, [
                'identifier' => $identifier,
            ]);
            return;
        }

        if (!$this->service()->attempt($identifier, $password, $request)) {
            $this->redirectWithFormState($request, $response, '/login', [
                'identifier' => 'The provided credentials do not match our records.',
            ], [
                'identifier' => $identifier,
            ]);
            return;
        }

        $request->setSessionValue('flash', 'Login successful.');
        $response->redirect('/dashboard');
    }

    public function logout(Request $request, Response $response): void
    {
        $this->service()->logout($request);
        $response->redirect('/login');
    }

    private function service(): AuthServiceInterface
    {
        if ($this->authService instanceof AuthServiceInterface) {
            return $this->authService;
        }

        $database = new Database(require config_path('database.php'));
        $repository = new UserRepository($database);

        return new AuthService($repository);
    }

    private function consumeFormState(Request $request): array
    {
        return [
            'errors' => (array) $request->pullSessionValue('errors', []),
            'old' => (array) $request->pullSessionValue('old', []),
            'flash' => (string) $request->pullSessionValue('flash', ''),
        ];
    }

    private function redirectWithFormState(Request $request, Response $response, string $path, array $errors, array $old): void
    {
        $request->setSessionValue('errors', $errors);
        $request->setSessionValue('old', $old);
        $response->redirect($path);
    }
}