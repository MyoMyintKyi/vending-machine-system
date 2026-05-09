<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\TransactionController;
use App\Interfaces\TransactionServiceInterface;
use Core\Request;
use Core\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TransactionControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testIndexRendersTransactionsModule(): void
    {
        $session = ['role' => 'Admin'];
        $_SESSION = $session;
        $request = $this->makeRequest($session, 'GET', '/transactions', [
            'page' => 2,
            'transaction_type' => 'PURCHASE',
            'username' => 'alice',
            'product_name' => 'Coke',
        ]);
        $response = new Response();
        $service = $this->createTransactionServiceMock();
        $service->expects($this->once())
            ->method('countFiltered')
            ->with([
                'transaction_type' => 'PURCHASE',
                'username' => 'alice',
                'product_name' => 'Coke',
            ])
            ->willReturn(12);
        $service->expects($this->once())
            ->method('getOverview')
            ->with(2, 10, [
                'transaction_type' => 'PURCHASE',
                'username' => 'alice',
                'product_name' => 'Coke',
            ])
            ->willReturn([
                'metrics' => [
                    'total_transactions' => 12,
                    'total_quantity' => 5,
                    'total_revenue' => '10.980',
                    'unique_users' => 2,
                ],
                'transactions' => [
                    [
                        'id' => 15,
                        'created_at' => '2026-05-09 10:00:00',
                        'username' => 'alice',
                        'product_name' => 'Coke',
                        'quantity' => 2,
                        'unit_price' => '3.990',
                        'total_amount' => '7.980',
                        'transaction_type' => 'PURCHASE',
                    ],
                ],
            ]);

        $controller = new TransactionController($service);

        $output = '';
        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            $output = (string) ob_get_clean();
        }

        $this->assertSame('transactions/index', $response->viewName());
        $this->assertSame(12, $response->viewData()['metrics']['total_transactions']);
        $this->assertSame(2, $response->viewData()['page']);
        $this->assertStringContainsString('Transactions Module', $output);
        $this->assertStringContainsString('Transaction Date', $output);
        $this->assertStringContainsString('alice', $output);
        $this->assertStringContainsString('name="username" type="text" value="alice"', $output);
        $this->assertStringContainsString('Showing 11 - 11 | Page 2 of 2', $output);
        $this->assertStringContainsString('/transactions?transaction_type=PURCHASE&amp;username=alice&amp;product_name=Coke&amp;page=1', $output);
    }

    public function testIndexShowsEmptyFilteredState(): void
    {
        $session = ['role' => 'Admin'];
        $_SESSION = $session;
        $request = $this->makeRequest($session, 'GET', '/transactions', [
            'username' => 'missing-user',
        ]);
        $response = new Response();
        $service = $this->createTransactionServiceMock();
        $service->expects($this->once())
            ->method('countFiltered')
            ->with([
                'transaction_type' => '',
                'username' => 'missing-user',
                'product_name' => '',
            ])
            ->willReturn(0);
        $service->expects($this->once())
            ->method('getOverview')
            ->with(1, 10, [
                'transaction_type' => '',
                'username' => 'missing-user',
                'product_name' => '',
            ])
            ->willReturn([
                'metrics' => [
                    'total_transactions' => 0,
                    'total_quantity' => 0,
                    'total_revenue' => '0.000',
                    'unique_users' => 0,
                ],
                'transactions' => [],
            ]);

        $controller = new TransactionController($service);

        $output = '';
        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            $output = (string) ob_get_clean();
        }

        $this->assertSame('transactions/index', $response->viewName());
        $this->assertStringContainsString('No transactions matched the current filters.', $output);
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

    /** @return TransactionServiceInterface&MockObject */
    private function createTransactionServiceMock(): TransactionServiceInterface
    {
        return $this->createMock(TransactionServiceInterface::class);
    }
}