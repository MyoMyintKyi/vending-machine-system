<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\AuthController;
use App\Interfaces\AuthServiceInterface;
use Core\Request;
use Core\Response;
use DomainException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testLoginFormRendersForGuest(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'GET', '/login');
        $response = new Response();
        $controller = new AuthController($this->createAuthServiceMock());

        ob_start();
        $controller->loginForm($request, $response);
        $output = (string) ob_get_clean();

        $this->assertSame('auth/login', $response->viewName());
        $this->assertStringContainsString('<h1>Login</h1>', $output);
        $this->assertStringContainsString('<form method="post" action="/login">', $output);
    }

    public function testLoginRedirectsAuthenticatedUserToDashboard(): void
    {
        $session = ['authenticated' => true];
        $request = $this->makeRequest($session, 'GET', '/login');
        $response = new Response();
        $controller = new AuthController($this->createAuthServiceMock());

        $controller->loginForm($request, $response);

        $this->assertSame('/dashboard', $response->redirectLocation());
        $this->assertSame(302, $response->statusCode());
    }

    public function testLoginRedirectsBackWithValidationErrors(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/login', [
            'identifier' => '',
            'password' => '',
        ]);
        $response = new Response();
        $controller = new AuthController($this->createAuthServiceMock());

        $controller->login($request, $response);

        $this->assertSame('/login', $response->redirectLocation());
        $this->assertSame('Username or email is required.', $session['errors']['identifier']);
        $this->assertSame('Password is required.', $session['errors']['password']);
    }

    public function testLoginRedirectsToDashboardOnSuccess(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/login', [
            'identifier' => 'admin@example.com',
            'password' => 'secret123',
        ]);
        $response = new Response();
        $service = $this->createAuthServiceMock();
        $service->expects($this->once())
            ->method('attempt')
            ->with('admin@example.com', 'secret123', $request)
            ->willReturn(true);

        $controller = new AuthController($service);

        $controller->login($request, $response);

        $this->assertSame('/dashboard', $response->redirectLocation());
        $this->assertSame('Login successful.', $session['flash']);
    }

    public function testRegisterFormRendersForGuest(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'GET', '/register');
        $response = new Response();
        $controller = new AuthController($this->createAuthServiceMock());

        ob_start();
        $controller->registerForm($request, $response);
        $output = (string) ob_get_clean();

        $this->assertSame('auth/register', $response->viewName());
        $this->assertStringContainsString('<h1>Register</h1>', $output);
        $this->assertStringContainsString('<form method="post" action="/register">', $output);
    }

    public function testRegisterRedirectsBackWithValidationErrors(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/register', [
            'username' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);
        $response = new Response();
        $controller = new AuthController($this->createAuthServiceMock());

        $controller->register($request, $response);

        $this->assertSame('/register', $response->redirectLocation());
        $this->assertSame('Username is required.', $session['errors']['username']);
        $this->assertSame('A valid email address is required.', $session['errors']['email']);
        $this->assertSame('Password must be at least 8 characters.', $session['errors']['password']);
        $this->assertSame('Password confirmation must match.', $session['errors']['password_confirmation']);
    }

    public function testRegisterRedirectsToLoginOnSuccess(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/register', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response = new Response();
        $service = $this->createAuthServiceMock();
        $service->expects($this->once())
            ->method('register')
            ->with([
                'username' => 'newuser',
                'email' => 'newuser@example.com',
                'password' => 'password123',
            ])
            ->willReturn([
                'id' => 10,
                'username' => 'newuser',
                'email' => 'newuser@example.com',
                'role' => 'User',
            ]);

        $controller = new AuthController($service);

        $controller->register($request, $response);

        $this->assertSame('/login', $response->redirectLocation());
        $this->assertSame('Registration successful. Please log in.', $session['flash']);
    }

    public function testRegisterRedirectsBackWhenServiceThrowsDomainException(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/register', [
            'username' => 'takenuser',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response = new Response();
        $service = $this->createAuthServiceMock();
        $service->method('register')->willThrowException(new DomainException('That username is already in use.'));

        $controller = new AuthController($service);

        $controller->register($request, $response);

        $this->assertSame('/register', $response->redirectLocation());
        $this->assertSame('That username is already in use.', $session['errors']['form']);
    }

    public function testLogoutCallsServiceAndRedirectsToLogin(): void
    {
        $session = [
            'authenticated' => true,
            'user_id' => 1,
        ];
        $request = $this->makeRequest($session, 'POST', '/logout', [], $session);
        $response = new Response();
        $service = $this->createAuthServiceMock();
        $service->expects($this->once())
            ->method('logout')
            ->with($request);

        $controller = new AuthController($service);

        $controller->logout($request, $response);

        $this->assertSame('/login', $response->redirectLocation());
    }

    private function makeRequest(array &$session, string $method, string $uri, array $post = [], ?array $sessionSeed = null): Request
    {
        if ($sessionSeed !== null) {
            $session = $sessionSeed;
            $_SESSION = $sessionSeed;
        }

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

    /** @return AuthServiceInterface&MockObject */
    private function createAuthServiceMock(): AuthServiceInterface
    {
        return $this->createMock(AuthServiceInterface::class);
    }
}