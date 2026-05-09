<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Request;
use Core\Response;
use Core\Router;
use PHPUnit\Framework\TestCase;

final class WebAuthRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testGetLoginRouteDispatchesLoginForm(): void
    {
        $session = [];
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'GET', '/login');
        $response = new Response();

        ob_start();
        $router->dispatch($request, $response);
        $output = (string) ob_get_clean();

        $this->assertSame('auth/login', $response->viewName());
        $this->assertStringContainsString('<h1>Login</h1>', $output);
    }

    public function testPostLoginRouteDispatchesValidationFailure(): void
    {
        $session = [];
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'POST', '/login', [
            'identifier' => '',
            'password' => '',
        ]);
        $response = new Response();

        $router->dispatch($request, $response);

        $this->assertSame('/login', $response->redirectLocation());
        $this->assertSame('Username or email is required.', $session['errors']['identifier']);
        $this->assertSame('Password is required.', $session['errors']['password']);
    }

    public function testGetRegisterRouteDispatchesRegisterForm(): void
    {
        $session = [];
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'GET', '/register');
        $response = new Response();

        ob_start();
        $router->dispatch($request, $response);
        $output = (string) ob_get_clean();

        $this->assertSame('auth/register', $response->viewName());
        $this->assertStringContainsString('<h1>Register</h1>', $output);
    }

    public function testPostRegisterRouteDispatchesValidationFailure(): void
    {
        $session = [];
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'POST', '/register', [
            'username' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);
        $response = new Response();

        $router->dispatch($request, $response);

        $this->assertSame('/register', $response->redirectLocation());
        $this->assertSame('Username is required.', $session['errors']['username']);
        $this->assertSame('A valid email address is required.', $session['errors']['email']);
        $this->assertSame('Password must be at least 8 characters.', $session['errors']['password']);
        $this->assertSame('Password confirmation must match.', $session['errors']['password_confirmation']);
    }

    public function testPostLogoutRouteRedirectsGuestsToLogin(): void
    {
        $session = [];
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'POST', '/logout');
        $response = new Response();

        $router->dispatch($request, $response);

        $this->assertSame('/login', $response->redirectLocation());
        $this->assertSame('Please log in to continue.', $session['flash']);
    }

    public function testPostLogoutRouteLogsOutAuthenticatedUser(): void
    {
        $session = [
            'authenticated' => true,
            'user_id' => 1,
            'username' => 'user',
            'role' => 'User',
        ];
        $_SESSION = $session;

        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'POST', '/logout');
        $response = new Response();

        $router->dispatch($request, $response);

        $this->assertSame('/login', $response->redirectLocation());
        $this->assertSame([], $session);
        $this->assertSame([], $_SESSION);
    }

    private function makeRouter(): Router
    {
        $router = new Router();

        require base_path('routes/web.php');

        return $router;
    }

    private function makeRequest(array &$session, string $method, string $uri, array $post = []): Request
    {
        return new Request(
            [],
            $post,
            [
                'REQUEST_METHOD' => $method,
                'REQUEST_URI' => $uri,
            ],
            [],
            [],
            $session
        );
    }
}