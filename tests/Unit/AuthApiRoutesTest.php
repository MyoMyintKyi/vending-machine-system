<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Request;
use Core\Response;
use Core\Router;
use PHPUnit\Framework\TestCase;

final class AuthApiRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testPostRegisterRouteReturnsValidationErrors(): void
    {
        $session = [];
        $router = $this->makeRouter();
        $request = $this->makeRequest($session, 'POST', '/api/auth/register', [
            'username' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ]);
        $response = new Response();

        ob_start();
        try {
            $router->dispatch($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();

        $this->assertSame(422, $response->statusCode());
        $this->assertFalse($payload['success']);
        $this->assertSame('Validation failed.', $payload['message']);
        $this->assertSame('Username is required.', $payload['errors']['username']);
        $this->assertSame('Email must be a valid email address.', $payload['errors']['email']);
        $this->assertSame('Password must be at least 8 characters.', $payload['errors']['password']);
    }

    private function makeRouter(): Router
    {
        $router = new Router();

        require base_path('routes/api.php');

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