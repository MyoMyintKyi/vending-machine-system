<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Request;
use Core\Response;
use Core\Router;
use PHPUnit\Framework\TestCase;

final class ProductApiRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testProductPurchaseApiRouteRejectsMissingBearerToken(): void
    {
        $session = [];
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'POST', '/api/products/1/purchase', [
            'quantity' => '1',
        ]);
        $response = new Response();

        ob_start();
        try {
            $router->dispatch($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();

        $this->assertSame(401, $response->statusCode());
        $this->assertFalse($payload['success']);
        $this->assertSame('Missing or invalid bearer token.', $payload['message']);
    }
    private function makeRouter(): Router
    {
        $router = new Router();

        require base_path('routes/api.php');

        return $router;
    }

    private function makeRequest(array &$session, string $method, string $uri, array $post = [], ?string $bearerToken = null): Request
    {
        $server = [
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $uri,
        ];

        if ($bearerToken !== null) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $bearerToken;
        }

        return new Request(
            [],
            $post,
            $server,
            [],
            [],
            $session
        );
    }
}