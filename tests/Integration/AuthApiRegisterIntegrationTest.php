<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Controllers\Api\AuthApiController;
use Core\Database;
use Core\Request;
use Core\Response;
use PHPUnit\Framework\TestCase;

final class AuthApiRegisterIntegrationTest extends TestCase
{
    private Database $database;

    private string $username;

    private string $email;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = new Database(require config_path('database.php'));
        $suffix = bin2hex(random_bytes(4));
        $this->username = 'integration_' . $suffix;
        $this->email = 'integration_' . $suffix . '@example.test';

        $this->cleanupUser();
    }

    protected function tearDown(): void
    {
        $this->cleanupUser();

        parent::tearDown();
    }

    public function testRegisterCreatesUserInIsolatedDatabase(): void
    {
        $session = [];
        $request = new Request(
            [],
            [
                'username' => $this->username,
                'email' => $this->email,
                'password' => 'password123',
            ],
            [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/api/auth/register',
            ],
            [],
            [],
            $session
        );
        $response = new Response();
        $controller = new AuthApiController();

        ob_start();
        try {
            $controller->register($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();
        $user = $this->database->query(
            'SELECT id, username, email, password_hash, role FROM users WHERE email = :email LIMIT 1',
            ['email' => $this->email]
        )->fetch();

        $this->assertSame(201, $response->statusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame('Registered successfully.', $payload['message']);
        $this->assertIsArray($user);
        $this->assertSame($this->username, $user['username']);
        $this->assertSame($this->email, $user['email']);
        $this->assertSame('User', $user['role']);
        $this->assertTrue(password_verify('password123', $user['password_hash']));
        $this->assertIsString($payload['data']['token']);
        $this->assertNotSame('', $payload['data']['token']);
    }

    private function cleanupUser(): void
    {
        $this->database->query(
            'DELETE FROM users WHERE email = :email',
            ['email' => $this->email]
        );
    }
}