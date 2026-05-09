<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\UsersController;
use App\Interfaces\UserServiceInterface;
use Core\Request;
use Core\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UsersControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testIndexRendersUsersModuleWithFiltersAndSort(): void
    {
        $session = ['role' => 'Admin'];
        $_SESSION = $session;
        $request = $this->makeRequest($session, 'GET', '/users', [
            'page' => 2,
            'role' => 'Admin',
            'username' => 'ad',
            'email' => 'example',
            'sort' => 'username',
            'direction' => 'asc',
        ]);
        $response = new Response();
        $service = $this->createUserServiceMock();
        $service->expects($this->once())
            ->method('countFiltered')
            ->with([
                'username' => 'ad',
                'email' => 'example',
                'role' => 'Admin',
            ])
            ->willReturn(12);
        $service->expects($this->once())
            ->method('getOverview')
            ->with(2, 10, [
                'username' => 'ad',
                'email' => 'example',
                'role' => 'Admin',
            ], 'username', 'asc')
            ->willReturn([
                'metrics' => [
                    'total_users' => 12,
                    'total_admins' => 3,
                    'total_standard_users' => 9,
                    'latest_joined_at' => '2026-05-10 09:00:00',
                ],
                'users' => [
                    [
                        'id' => 1,
                        'username' => 'admin',
                        'email' => 'admin@example.com',
                        'role' => 'Admin',
                        'created_at' => '2026-05-10 09:00:00',
                    ],
                ],
            ]);

        $controller = new UsersController($service);

        $output = '';
        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            $output = (string) ob_get_clean();
        }

        $this->assertSame('users/index', $response->viewName());
        $this->assertSame('Admin', $response->viewData()['filters']['role']);
        $this->assertSame('username', $response->viewData()['sort']);
        $this->assertSame('asc', $response->viewData()['direction']);
        $this->assertSame(2, $response->viewData()['page']);
        $this->assertStringContainsString('Users Module', $output);
        $this->assertStringContainsString('admin@example.com', $output);
        $this->assertStringContainsString('Showing 11 - 11 | Page 2 of 2', $output);
        $this->assertStringContainsString('/users?username=ad&amp;email=example&amp;role=Admin&amp;sort=username&amp;direction=asc&amp;page=1', $output);
    }

    public function testIndexShowsEmptyFilteredState(): void
    {
        $session = ['role' => 'Admin'];
        $_SESSION = $session;
        $request = $this->makeRequest($session, 'GET', '/users', [
            'email' => 'missing@example.com',
        ]);
        $response = new Response();
        $service = $this->createUserServiceMock();
        $service->expects($this->once())
            ->method('countFiltered')
            ->with([
                'username' => '',
                'email' => 'missing@example.com',
                'role' => '',
            ])
            ->willReturn(0);
        $service->expects($this->once())
            ->method('getOverview')
            ->with(1, 10, [
                'username' => '',
                'email' => 'missing@example.com',
                'role' => '',
            ], 'created_at', 'desc')
            ->willReturn([
                'metrics' => [
                    'total_users' => 0,
                    'total_admins' => 0,
                    'total_standard_users' => 0,
                    'latest_joined_at' => '-',
                ],
                'users' => [],
            ]);

        $controller = new UsersController($service);

        $output = '';
        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            $output = (string) ob_get_clean();
        }

        $this->assertSame('users/index', $response->viewName());
        $this->assertStringContainsString('No users matched the current filters.', $output);
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

    /** @return UserServiceInterface&MockObject */
    private function createUserServiceMock(): UserServiceInterface
    {
        return $this->createMock(UserServiceInterface::class);
    }
}