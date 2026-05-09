<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Interfaces\AuthServiceInterface;
use Core\Database;
use Core\Request;
use Core\Response;
use DomainException;

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

        $response->view('auth/login', $this->consumeFormState($request));
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

    public function registerForm(Request $request, Response $response): void
    {
        if ((bool) $request->session('authenticated', false) === true) {
            $response->redirect('/dashboard');
            return;
        }

        $response->view('auth/register', $this->consumeFormState($request));
    }

    public function register(Request $request, Response $response): void
    {
        $username = trim((string) $request->input('username', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $passwordConfirmation = (string) $request->input('password_confirmation', '');

        $errors = [];

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'A valid email address is required.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($passwordConfirmation === '') {
            $errors['password_confirmation'] = 'Password confirmation is required.';
        } elseif ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Password confirmation must match.';
        }

        if ($errors !== []) {
            $this->redirectWithFormState($request, $response, '/register', $errors, [
                'username' => $username,
                'email' => $email,
            ]);
            return;
        }

        try {
            $this->service()->register([
                'username' => $username,
                'email' => $email,
                'password' => $password,
            ]);
        } catch (DomainException $exception) {
            $this->redirectWithFormState($request, $response, '/register', [
                'form' => $exception->getMessage(),
            ], [
                'username' => $username,
                'email' => $email,
            ]);
            return;
        }

        $request->setSessionValue('flash', 'Registration successful. Please log in.');
        $response->redirect('/login');
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