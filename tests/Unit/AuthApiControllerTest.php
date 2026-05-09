<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Api\AuthApiController;
use App\Interfaces\UserRepositoryInterface;
use App\Services\JwtService;
use Core\Request;
use Core\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AuthApiControllerTest extends TestCase
{
    public function testRegisterReturnsValidationErrors(): void
    {
        $request = $this->makeRequest([
            'username' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ]);
        $response = new Response();
        $controller = new AuthApiController($this->createUserRepositoryMock(), new JwtService('unit-test-secret', 3600));

        ob_start();
        try {
            $controller->register($request, $response);
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

    public function testRegisterCreatesUserAndReturnsToken(): void
    {
        $request = $this->makeRequest([
            'username' => 'new-user',
            'email' => 'new-user@example.com',
            'password' => 'password123',
        ]);
        $response = new Response();
        $repository = $this->createUserRepositoryMock();
        $jwtService = new JwtService('unit-test-secret', 3600);

        $repository->expects($this->once())
            ->method('findByUsername')
            ->with('new-user')
            ->willReturn(null);
        $repository->expects($this->once())
            ->method('findByEmail')
            ->with('new-user@example.com')
            ->willReturn(null);
        $repository->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $data): bool {
                return $data['username'] === 'new-user'
                    && $data['email'] === 'new-user@example.com'
                    && $data['role'] === 'User'
                    && is_string($data['password_hash'])
                    && password_verify('password123', $data['password_hash']);
            }))
            ->willReturn(5);
        $repository->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn([
                'id' => 5,
                'username' => 'new-user',
                'email' => 'new-user@example.com',
                'role' => 'User',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            ]);

        $controller = new AuthApiController($repository, $jwtService);

        ob_start();
        try {
            $controller->register($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();

        $this->assertSame(201, $response->statusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame('Registered successfully.', $payload['message']);
        $this->assertSame('Bearer', $payload['data']['token_type']);
        $this->assertSame(3600, $payload['data']['expires_in']);
        $this->assertSame([
            'id' => 5,
            'username' => 'new-user',
            'email' => 'new-user@example.com',
            'role' => 'User',
        ], $payload['data']['user']);
        $this->assertIsString($payload['data']['token']);
        $this->assertNotSame('', $payload['data']['token']);
    }

    private function makeRequest(array $post = []): Request
    {
        $session = [];

        return new Request(
            [],
            $post,
            [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/api/auth/register',
            ],
            [],
            [],
            $session
        );
    }

    /** @return UserRepositoryInterface&MockObject */
    private function createUserRepositoryMock(): UserRepositoryInterface
    {
        return $this->createMock(UserRepositoryInterface::class);
    }
}