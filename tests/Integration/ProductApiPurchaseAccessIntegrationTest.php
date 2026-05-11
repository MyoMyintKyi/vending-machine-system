<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\JwtService;
use Core\Database;
use Core\Request;
use Core\Response;
use Core\Router;
use PHPUnit\Framework\TestCase;

final class ProductApiPurchaseAccessIntegrationTest extends TestCase
{
    private Database $database;

    private array $createdUserEmails = [];

    private array $createdProductNames = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = new Database(require config_path('database.php'));
    }

    protected function tearDown(): void
    {
        foreach ($this->createdProductNames as $name) {
            $this->database->query('DELETE FROM products WHERE name = :name', ['name' => $name]);
        }

        foreach ($this->createdUserEmails as $email) {
            $this->database->query('DELETE FROM users WHERE email = :email', ['email' => $email]);
        }

        parent::tearDown();
    }

    public function testPurchaseApiRouteForbidsAdmins(): void
    {
        $session = [];
        $admin = $this->createUser('Admin');
        $router = $this->makeRouter();
        $request = $this->makeRequest(
            $session,
            'POST',
            '/api/products/1/purchase',
            ['quantity' => '1'],
            $this->bearerTokenFor($admin)
        );
        $response = new Response();

        ob_start();
        try {
            $router->dispatch($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();

        $this->assertSame(403, $response->statusCode());
        $this->assertFalse($payload['success']);
        $this->assertSame('You do not have permission to access this resource.', $payload['message']);
    }

    public function testPurchaseApiRouteAllowsUsersToReachController(): void
    {
        $session = [];
        $user = $this->createUser('User');
        $product = $this->createProduct();
        $router = $this->makeRouter();
        $request = $this->makeRequest(
            $session,
            'POST',
            '/api/products/' . $product['id'] . '/purchase',
            ['quantity' => '0'],
            $this->bearerTokenFor($user)
        );
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
        $this->assertSame('Quantity must be an integer greater than or equal to 1.', $payload['errors']['quantity']);
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

    private function bearerTokenFor(array $user): string
    {
        return (new JwtService())->issueToken($user);
    }

    private function createUser(string $role): array
    {
        $suffix = strtolower($role) . '_' . bin2hex(random_bytes(4));
        $email = $suffix . '@example.test';
        $repository = new UserRepository($this->database);
        $userId = $repository->create([
            'username' => $suffix,
            'email' => $email,
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => $role,
        ]);
        $user = $repository->findById($userId);

        $this->createdUserEmails[] = $email;
        $this->assertIsArray($user);

        return $user;
    }

    private function createProduct(): array
    {
        $name = 'Integration Product ' . bin2hex(random_bytes(4));
        $repository = new ProductRepository($this->database);
        $productId = $repository->create([
            'name' => $name,
            'price' => '1.000',
            'quantity_available' => 5,
        ]);
        $product = $repository->findById($productId);

        $this->createdProductNames[] = $name;
        $this->assertIsArray($product);

        return $product;
    }
}