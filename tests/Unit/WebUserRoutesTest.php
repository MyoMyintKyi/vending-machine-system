<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Request;
use Core\Response;
use Core\Router;
use PHPUnit\Framework\TestCase;

final class WebUserRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testUsersRouteRedirectsGuestsToLogin(): void
    {
        $session = [];
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'GET', '/users');
        $response = new Response();

        $router->dispatch($request, $response);

        $this->assertSame('/login', $response->redirectLocation());
        $this->assertSame('Please log in to continue.', $session['flash']);
    }

    public function testUsersRouteForbidsNonAdminUsers(): void
    {
        $session = [
            'authenticated' => true,
            'role' => 'User',
        ];
        $_SESSION = $session;
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'GET', '/users');
        $response = new Response();

        ob_start();
        try {
            $router->dispatch($request, $response);
        } finally {
            ob_end_clean();
        }

        $this->assertSame('auth/forbidden', $response->viewName());
    }

    private function makeRouter(): Router
    {
        $router = new Router();

        require base_path('routes/web.php');

        return $router;
    }

    private function makeRequest(array &$session, string $method, string $uri, array $query = []): Request
    {
        return new Request(
            $query,
            [],
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