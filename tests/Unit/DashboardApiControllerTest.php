<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Api\DashboardApiController;
use Core\Request;
use Core\Response;
use PHPUnit\Framework\TestCase;

final class DashboardApiControllerTest extends TestCase
{
    public function testIndexReturnsAuthenticatedDashboardDetails(): void
    {
        $request = $this->makeRequest();
        $request->setAttribute('auth.user', [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role' => 'Admin',
        ]);
        $response = new Response();
        $controller = new DashboardApiController();

        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();

        $this->assertTrue($payload['success']);
        $this->assertSame([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role' => 'Admin',
        ], $payload['data']);
        $this->assertSame('Dashboard retrieved successfully.', $payload['message']);
    }

    private function makeRequest(): Request
    {
        $session = [];

        return new Request(
            [],
            [],
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/api/dashboard',
            ],
            [],
            [],
            $session
        );
    }
}