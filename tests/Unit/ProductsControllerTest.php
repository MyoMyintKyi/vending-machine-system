<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\ProductsController;
use App\Interfaces\ProductServiceInterface;
use App\Interfaces\PurchaseServiceInterface;
use Core\Request;
use Core\Response;
use DomainException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ProductsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
    }

    public function testIndexRendersProductsList(): void
    {
        $session = ['role' => 'Admin'];
        $_SESSION = $session;
        $request = $this->makeRequest($session, 'GET', '/products', [
            'page' => 2,
            'sort' => 'price',
            'direction' => 'desc',
        ]);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->expects($this->once())
            ->method('countAll')
            ->willReturn(15);
        $service->expects($this->once())
            ->method('findAll')
            ->with(2, 10, 'price', 'desc')
            ->willReturn([$this->productRecord()]);

        $controller = new ProductsController($service);

        $output = '';
        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            $output = (string) ob_get_clean();
        }

        $this->assertSame('products/index', $response->viewName());
        $this->assertSame('Admin', $response->viewData()['role']);
        $this->assertCount(1, $response->viewData()['products']);
        $this->assertSame(2, $response->viewData()['page']);
        $this->assertSame(2, $response->viewData()['totalPages']);
        $this->assertStringContainsString('class="sort-link is-active"', $output);
        $this->assertStringContainsString('Products pagination', $output);
        $this->assertStringContainsString('class="pagination-link is-active"', $output);
        $this->assertStringContainsString('>11<', $output);
        $this->assertStringContainsString('Showing 11 - 11 | Page 2 of 2', $output);
    }

    public function testIndexCondensesPaginationWhenTotalPagesExceedTen(): void
    {
        $session = ['role' => 'Admin'];
        $_SESSION = $session;
        $request = $this->makeRequest($session, 'GET', '/products', [
            'page' => 22,
            'sort' => 'name',
            'direction' => 'asc',
        ]);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->expects($this->once())
            ->method('countAll')
            ->willReturn(240);
        $service->expects($this->once())
            ->method('findAll')
            ->with(22, 10, 'name', 'asc')
            ->willReturn([$this->productRecord()]);

        $controller = new ProductsController($service);

        $output = '';
        ob_start();
        try {
            $controller->index($request, $response);
        } finally {
            $output = (string) ob_get_clean();
        }

        $this->assertStringContainsString('>211<', $output);
        $this->assertStringContainsString('Showing 211 - 211 | Page 22 of 24', $output);
        $this->assertStringContainsString('>1<', $output);
        $this->assertStringContainsString('>2<', $output);
        $this->assertStringContainsString('>3<', $output);
        $this->assertStringContainsString('>21<', $output);
        $this->assertStringContainsString('class="pagination-link is-active" href="/products?page=22&sort=name&direction=asc">22</a>', $output);
        $this->assertStringContainsString('>23<', $output);
        $this->assertStringContainsString('>24<', $output);
        $this->assertStringContainsString('class="pagination-ellipsis">...</span>', $output);
    }

    public function testShowReturnsNotFoundWhenProductDoesNotExist(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'GET', '/products/99', [], [], ['id' => '99']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(99)->willReturn(null);

        $controller = new ProductsController($service);

        ob_start();
        try {
            $controller->show($request, $response);
        } finally {
            ob_end_clean();
        }

        $this->assertSame(404, $response->statusCode());
        $this->assertSame('Product not found.', $response->jsonPayload()['message']);
    }

    public function testShowRendersProductDetails(): void
    {
        $session = ['role' => 'Admin'];
        $_SESSION = $session;
        $request = $this->makeRequest($session, 'GET', '/products/1', [], [], ['id' => '1']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(1)->willReturn($this->productRecord());

        $controller = new ProductsController($service);

        ob_start();
        $controller->show($request, $response);
        ob_end_clean();

        $this->assertSame('products/show', $response->viewName());
        $this->assertSame('Coke', $response->viewData()['product']['name']);
    }

    public function testCreateRendersCreateView(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'GET', '/products/create');
        $response = new Response();

        $controller = new ProductsController($this->createProductServiceMock());

        ob_start();
        try {
            $controller->create($request, $response);
        } finally {
            ob_end_clean();
        }

        $this->assertSame('products/create', $response->viewName());
    }

    public function testStoreRedirectsBackWithValidationErrors(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/products', [], [
            'name' => '',
            'price' => '-1',
            'quantity_available' => '-2',
        ]);
        $response = new Response();

        $controller = new ProductsController($this->createProductServiceMock());

        $controller->store($request, $response);

        $this->assertSame('/products/create', $response->redirectLocation());
        $this->assertSame('Product name is required.', $session['errors']['name']);
        $this->assertSame('Price must be a number greater than 0.', $session['errors']['price']);
        $this->assertSame('Quantity available must be a non-negative integer.', $session['errors']['quantity_available']);
    }

    public function testStoreCreatesProductAndRedirectsToShowPage(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/products', [], [
            'name' => 'Sprite',
            'price' => '2.500',
            'quantity_available' => '7',
        ]);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->expects($this->once())
            ->method('create')
            ->with([
                'name' => 'Sprite',
                'price' => '2.500',
                'quantity_available' => '7',
            ])
            ->willReturn(5);

        $controller = new ProductsController($service);

        $controller->store($request, $response);

        $this->assertSame('/products/5', $response->redirectLocation());
        $this->assertSame('Product created successfully.', $session['flash']);
    }

    public function testStoreRedirectsBackWhenServiceFails(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/products', [], [
            'name' => 'Sprite',
            'price' => '2.500',
            'quantity_available' => '7',
        ]);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('create')->willThrowException(new RuntimeException('create failed'));

        $controller = new ProductsController($service);

        $controller->store($request, $response);

        $this->assertSame('/products/create', $response->redirectLocation());
        $this->assertSame('Product could not be created. Please verify the details and try again.', $session['errors']['form']);
    }

    public function testEditReturnsNotFoundWhenProductDoesNotExist(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'GET', '/products/99/edit', [], [], ['id' => '99']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(99)->willReturn(null);

        $controller = new ProductsController($service);

        ob_start();
        try {
            $controller->edit($request, $response);
        } finally {
            ob_end_clean();
        }

        $this->assertSame(404, $response->statusCode());
    }

    public function testEditRendersEditView(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'GET', '/products/1/edit', [], [], ['id' => '1']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(1)->willReturn($this->productRecord());

        $controller = new ProductsController($service);

        ob_start();
        $controller->edit($request, $response);
        ob_end_clean();

        $this->assertSame('products/edit', $response->viewName());
        $this->assertSame(1, $response->viewData()['product']['id']);
    }

    public function testUpdateRedirectsBackWithValidationErrors(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/products/1/update', [], [
            'name' => '',
            'price' => '0',
            'quantity_available' => '-1',
        ], ['id' => '1']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(1)->willReturn($this->productRecord());

        $controller = new ProductsController($service);

        $controller->update($request, $response);

        $this->assertSame('/products/1/edit', $response->redirectLocation());
        $this->assertSame('Product name is required.', $session['errors']['name']);
    }

    public function testUpdateRedirectsToProductPageOnSuccess(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/products/1/update', [], [
            'name' => 'Updated Coke',
            'price' => '4.000',
            'quantity_available' => '12',
        ], ['id' => '1']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(1)->willReturn($this->productRecord());
        $service->expects($this->once())
            ->method('update')
            ->with(1, [
                'name' => 'Updated Coke',
                'price' => '4.000',
                'quantity_available' => '12',
            ])
            ->willReturn(true);

        $controller = new ProductsController($service);

        $controller->update($request, $response);

        $this->assertSame('/products/1', $response->redirectLocation());
        $this->assertSame('Product updated successfully.', $session['flash']);
    }

    public function testDestroyDeletesProductAndRedirectsToIndex(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/products/1/delete', [], [], ['id' => '1']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(1)->willReturn($this->productRecord());
        $service->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $controller = new ProductsController($service);

        $controller->destroy($request, $response);

        $this->assertSame('/products', $response->redirectLocation());
        $this->assertSame('Product deleted successfully.', $session['flash']);
    }

    public function testPurchaseFormRendersWhenProductExists(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'GET', '/products/1/purchase', [], [], ['id' => '1']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(1)->willReturn($this->productRecord());
        $purchaseService = $this->createPurchaseServiceMock();

        $controller = new ProductsController($service, $purchaseService);

        ob_start();
        try {
            $controller->purchaseForm($request, $response);
        } finally {
            ob_end_clean();
        }

        $this->assertSame('products/purchase', $response->viewName());
        $this->assertSame('Coke', $response->viewData()['product']['name']);
    }

    public function testPurchaseRedirectsBackWithValidationErrors(): void
    {
        $session = [];
        $request = $this->makeRequest($session, 'POST', '/products/1/purchase', [], [
            'quantity' => '0',
        ], ['id' => '1']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(1)->willReturn($this->productRecord());
        $purchaseService = $this->createPurchaseServiceMock();

        $controller = new ProductsController($service, $purchaseService);

        $controller->purchase($request, $response);

        $this->assertSame('/products/1/purchase', $response->redirectLocation());
        $this->assertSame('Quantity must be an integer greater than or equal to 1.', $session['errors']['quantity']);
    }

    public function testPurchaseRedirectsBackWhenPurchaseServiceRejectsRequest(): void
    {
        $session = ['user_id' => 7];
        $request = $this->makeRequest($session, 'POST', '/products/1/purchase', [], [
            'quantity' => '4',
        ], ['id' => '1']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(1)->willReturn($this->productRecord());
        $purchaseService = $this->createPurchaseServiceMock();
        $purchaseService->method('purchase')->willThrowException(new DomainException('Requested quantity exceeds available stock.'));

        $controller = new ProductsController($service, $purchaseService);

        $controller->purchase($request, $response);

        $this->assertSame('/products/1/purchase', $response->redirectLocation());
        $this->assertSame('Requested quantity exceeds available stock.', $session['errors']['quantity']);
    }

    public function testPurchaseCompletesSuccessfully(): void
    {
        $session = ['user_id' => 7];
        $request = $this->makeRequest($session, 'POST', '/products/1/purchase', [], [
            'quantity' => '2',
        ], ['id' => '1']);
        $response = new Response();
        $service = $this->createProductServiceMock();
        $service->method('findById')->with(1)->willReturn($this->productRecord());
        $purchaseService = $this->createPurchaseServiceMock();
        $purchaseService->expects($this->once())
            ->method('purchase')
            ->with(7, 1, 2)
            ->willReturn([
                'transaction_id' => 15,
                'product_id' => 1,
                'quantity' => 2,
                'unit_price' => '3.990',
                'total_amount' => '7.980',
            ]);

        $controller = new ProductsController($service, $purchaseService);

        $controller->purchase($request, $response);

        $this->assertSame('/products/1/purchase', $response->redirectLocation());
        $this->assertSame('Purchase completed successfully. Quantity: 2. Total: 7.980.', $session['flash']);
    }

    private function makeRequest(array &$session, string $method, string $uri, array $query = [], array $post = [], array $routeParams = []): Request
    {
        return new Request(
            $query,
            $post,
            [
                'REQUEST_METHOD' => $method,
                'REQUEST_URI' => $uri,
            ],
            [],
            [],
            $session,
            $routeParams
        );
    }

    /** @return ProductServiceInterface&MockObject */
    private function createProductServiceMock(): ProductServiceInterface
    {
        return $this->createMock(ProductServiceInterface::class);
    }

    /** @return PurchaseServiceInterface&MockObject */
    private function createPurchaseServiceMock(): PurchaseServiceInterface
    {
        return $this->createMock(PurchaseServiceInterface::class);
    }

    private function productRecord(): array
    {
        return [
            'id' => 1,
            'name' => 'Coke',
            'price' => '3.990',
            'quantity_available' => 10,
            'created_at' => '2026-05-09 10:00:00',
            'updated_at' => '2026-05-09 10:00:00',
        ];
    }
}