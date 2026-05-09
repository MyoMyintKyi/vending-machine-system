<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Request;
use Core\Response;
use Core\Router;
use PHPUnit\Framework\TestCase;

final class WebProductRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testProductsIndexRedirectsGuestsToLogin(): void
    {
        $session = [];
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'GET', '/products');
        $response = new Response();

        $router->dispatch($request, $response);

        $this->assertSame('/login', $response->redirectLocation());
        $this->assertSame('Please log in to continue.', $session['flash']);
    }

    public function testProductCreateRouteForbidsNonAdminUsers(): void
    {
        $session = [
            'authenticated' => true,
            'role' => 'User',
        ];
        $_SESSION = $session;
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'GET', '/products/create');
        $response = new Response();

        ob_start();
        $router->dispatch($request, $response);
        ob_end_clean();

        $this->assertSame('auth/forbidden', $response->viewName());
        $this->assertSame('Admin', $response->viewData()['requiredRole']);
        $this->assertSame('User', $response->viewData()['currentRole']);
    }

    public function testProductCreateRouteRendersForAdmins(): void
    {
        $session = [
            'authenticated' => true,
            'username' => 'admin',
            'role' => 'Admin',
        ];
        $_SESSION = $session;
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'GET', '/products/create');
        $response = new Response();

        ob_start();
        $router->dispatch($request, $response);
        $output = (string) ob_get_clean();

        $this->assertSame('products/create', $response->viewName());
        $this->assertStringContainsString('Signed in as admin (Admin)', $output);
        $this->assertStringContainsString('Logout', $output);
    }

    public function testProductStoreRouteHandlesValidationFailureForAdmins(): void
    {
        $session = [
            'authenticated' => true,
            'role' => 'Admin',
        ];
        $_SESSION = $session;
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'POST', '/products', [], [
            'name' => '',
            'price' => '0',
            'quantity_available' => '-1',
        ]);
        $response = new Response();

        $router->dispatch($request, $response);

        $this->assertSame('/products/create', $response->redirectLocation());
        $this->assertArrayHasKey('name', $session['errors']);
    }

    public function testProductEditRouteForbidsNonAdminUsers(): void
    {
        $session = [
            'authenticated' => true,
            'role' => 'User',
        ];
        $_SESSION = $session;
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'GET', '/products/1/edit');
        $response = new Response();

        ob_start();
        $router->dispatch($request, $response);
        ob_end_clean();

        $this->assertSame('auth/forbidden', $response->viewName());
    }

    public function testProductUpdateRouteForbidsNonAdminUsers(): void
    {
        $session = [
            'authenticated' => true,
            'role' => 'User',
        ];
        $_SESSION = $session;
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'POST', '/products/1/update');
        $response = new Response();

        ob_start();
        $router->dispatch($request, $response);
        ob_end_clean();

        $this->assertSame('auth/forbidden', $response->viewName());
    }

    public function testProductDeleteRouteForbidsNonAdminUsers(): void
    {
        $session = [
            'authenticated' => true,
            'role' => 'User',
        ];
        $_SESSION = $session;
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'POST', '/products/1/delete');
        $response = new Response();

        ob_start();
        $router->dispatch($request, $response);
        ob_end_clean();

        $this->assertSame('auth/forbidden', $response->viewName());
    }

    public function testProductPurchaseRouteRedirectsGuestsToLogin(): void
    {
        $session = [];
        $router = $this->makeRouter();

        $getRequest = $this->makeRequest($session, 'GET', '/products/1/purchase');
        $getResponse = new Response();
        $router->dispatch($getRequest, $getResponse);

        $this->assertSame('/login', $getResponse->redirectLocation());
        $this->assertSame('Please log in to continue.', $session['flash']);
    }

    private function makeRouter(): Router
    {
        $router = new Router();

        require base_path('routes/web.php');

        return $router;
    }

    private function makeRequest(array &$session, string $method, string $uri, array $query = [], array $post = []): Request
    {
        return new Request(
            $query,
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