<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Api\UserApiController;
use App\Interfaces\UserServiceInterface;
use Core\Request;
use Core\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UserApiControllerTest extends TestCase
{
    public function testIndexReturnsPaginatedFilteredSortedUsers(): void
    {
        $request = $this->makeRequest([
            'page' => '2',
            'per_page' => '5',
            'username' => 'ad',
            'email' => 'example',
            'role' => 'Admin',
            'sort' => 'username',
            'direction' => 'asc',
        ]);
        $response = new Response();
        $service = $this->createServiceMock();
        $filters = [
            'username' => 'ad',
            'email' => 'example',
            'role' => 'Admin',
        ];
        $sort = [
            'field' => 'username',
            'direction' => 'asc',
        ];
        $items = [[
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role' => 'Admin',
            'created_at' => '2026-05-10 09:00:00',
            'updated_at' => '2026-05-10 09:00:00',
        ]];
        $metrics = [
            'total_users' => 7,
            'total_admins' => 2,
            'total_standard_users' => 5,
            'latest_joined_at' => '2026-05-10 09:00:00',
        ];

        $service->expects($this->once())
            ->method('countFiltered')
            ->with($filters)
            ->willReturn(7);
        $service->expects($this->once())
            ->method('getOverview')
            ->with(2, 5, $filters, 'username', 'asc')
            ->willReturn([
                'users' => $items,
                'metrics' => $metrics,
            ]);

        $controller = new UserApiController($service);

        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();

        $this->assertTrue($payload['success']);
        $this->assertSame($items, $payload['data']['items']);
        $this->assertSame($metrics, $payload['data']['metrics']);
        $this->assertSame([
            'page' => 2,
            'per_page' => 5,
            'total' => 7,
            'total_pages' => 2,
        ], $payload['data']['pagination']);
        $this->assertSame($filters, $payload['data']['filters']);
        $this->assertSame($sort, $payload['data']['sort']);
        $this->assertSame('Users retrieved successfully.', $payload['message']);
    }

    public function testIndexNormalizesUnsupportedRoleAndSortInputs(): void
    {
        $request = $this->makeRequest([
            'role' => 'Manager',
            'sort' => 'unknown',
            'direction' => 'sideways',
        ]);
        $response = new Response();
        $service = $this->createServiceMock();
        $filters = [
            'username' => '',
            'email' => '',
            'role' => '',
        ];
        $sort = [
            'field' => 'created_at',
            'direction' => 'desc',
        ];

        $service->expects($this->once())
            ->method('countFiltered')
            ->with($filters)
            ->willReturn(0);
        $service->expects($this->once())
            ->method('getOverview')
            ->with(1, 10, $filters, 'created_at', 'desc')
            ->willReturn([
                'users' => [],
                'metrics' => [
                    'total_users' => 0,
                    'total_admins' => 0,
                    'total_standard_users' => 0,
                    'latest_joined_at' => '-',
                ],
            ]);

        $controller = new UserApiController($service);

        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();

        $this->assertSame($filters, $payload['data']['filters']);
        $this->assertSame($sort, $payload['data']['sort']);
        $this->assertSame(1, $payload['data']['pagination']['page']);
        $this->assertSame(10, $payload['data']['pagination']['per_page']);
    }

    private function makeRequest(array $query = []): Request
    {
        $session = [];

        return new Request(
            $query,
            [],
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/api/users',
            ],
            [],
            [],
            $session
        );
    }

    /** @return UserServiceInterface&MockObject */
    private function createServiceMock(): UserServiceInterface
    {
        return $this->createMock(UserServiceInterface::class);
    }
}