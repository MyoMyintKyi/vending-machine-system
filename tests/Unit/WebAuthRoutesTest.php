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
        $this->assertStringContainsString('Use your username or email and password to sign in.', $output);
        $this->assertStringNotContainsString('Logout', $output);
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