<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Api\TransactionApiController;
use App\Interfaces\TransactionServiceInterface;
use Core\Request;
use Core\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TransactionApiControllerTest extends TestCase
{
    public function testIndexReturnsPaginatedAndFilteredTransactions(): void
    {
        $request = $this->makeRequest([
            'page' => '2',
            'per_page' => '5',
            'transaction_type' => 'PURCHASE',
            'username' => 'alice',
            'product_name' => 'Coke',
        ]);
        $response = new Response();
        $service = $this->createServiceMock();
        $filters = [
            'transaction_type' => 'PURCHASE',
            'username' => 'alice',
            'product_name' => 'Coke',
        ];
        $items = [[
            'id' => 15,
            'user_id' => 7,
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => '3.990',
            'total_amount' => '7.980',
            'transaction_type' => 'PURCHASE',
            'created_at' => '2026-05-09 10:00:00',
            'username' => 'alice',
            'product_name' => 'Coke',
        ]];

        $service->expects($this->once())
            ->method('countFiltered')
            ->with($filters)
            ->willReturn(7);
        $service->expects($this->once())
            ->method('getOverview')
            ->with(2, 5, $filters)
            ->willReturn([
                'transactions' => $items,
            ]);

        $controller = new TransactionApiController($service);

        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();

        $this->assertTrue($payload['success']);
        $this->assertSame($items, $payload['data']['items']);
        $this->assertSame([
            'page' => 2,
            'per_page' => 5,
            'total' => 7,
            'total_pages' => 2,
        ], $payload['data']['pagination']);
        $this->assertSame($filters, $payload['data']['filters']);
        $this->assertSame('Transactions retrieved successfully.', $payload['message']);
    }

    public function testIndexNormalizesUnsupportedTransactionTypeFilter(): void
    {
        $request = $this->makeRequest([
            'transaction_type' => 'INVALID',
        ]);
        $response = new Response();
        $service = $this->createServiceMock();
        $filters = [
            'transaction_type' => '',
            'username' => '',
            'product_name' => '',
        ];

        $service->expects($this->once())
            ->method('countFiltered')
            ->with($filters)
            ->willReturn(0);
        $service->expects($this->once())
            ->method('getOverview')
            ->with(1, 10, $filters)
            ->willReturn([
                'transactions' => [],
            ]);

        $controller = new TransactionApiController($service);

        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            ob_end_clean();
        }

        $payload = $response->jsonPayload();

        $this->assertSame($filters, $payload['data']['filters']);
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
                'REQUEST_URI' => '/api/transactions',
            ],
            [],
            [],
            $session
        );
    }

    /** @return TransactionServiceInterface&MockObject */
    private function createServiceMock(): TransactionServiceInterface
    {
        return $this->createMock(TransactionServiceInterface::class);
    }
}