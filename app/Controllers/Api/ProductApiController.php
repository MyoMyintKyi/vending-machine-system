<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Repositories\ProductRepository;
use App\Repositories\TransactionRepository;
use App\Support\CurrencyFormatter;
use App\Services\ProductService;
use App\Services\PurchaseService;
use Core\Database;
use Core\Request;
use Core\Response;
use DomainException;
use Throwable;

final class ProductApiController
{
    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));
        $sort = (string) $request->query('sort', 'name');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $service = $this->productService();
        $total = $service->countAll();

        $response->json([
            'success' => true,
            'data' => [
                'items' => array_map($this->formatProduct(...), $service->findAll($page, $perPage, $sort, $direction)),
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => max(1, (int) ceil($total / $perPage)),
                ],
                'sort' => [
                    'field' => $sort,
                    'direction' => $direction,
                ],
            ],
            'message' => 'Products retrieved successfully.',
        ]);
    }

    public function show(Request $request, Response $response): void
    {
        $product = $this->productService()->findById((int) $request->route('id', 0));

        if ($product === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        $response->json([
            'success' => true,
            'data' => $this->formatProduct($product),
            'message' => 'Product retrieved successfully.',
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $payload = $this->productPayload($request);
        $errors = $this->validateProductData($payload);

        if ($errors !== []) {
            $this->validationFailed($response, $errors);
            return;
        }

        try {
            $productId = $this->productService()->create($payload);
            $product = $this->productService()->findById($productId);
        } catch (Throwable) {
            $response->json([
                'success' => false,
                'message' => 'Product could not be created.',
            ], 500);
            return;
        }

        $response->json([
            'success' => true,
            'data' => $this->formatProduct($product ?? []),
            'message' => 'Product created successfully.',
        ], 201);
    }

    public function update(Request $request, Response $response): void
    {
        $productId = (int) $request->route('id', 0);
        $existing = $this->productService()->findById($productId);

        if ($existing === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        $payload = $this->productPayload($request);
        $errors = $this->validateProductData($payload);

        if ($errors !== []) {
            $this->validationFailed($response, $errors);
            return;
        }

        try {
            $this->productService()->update($productId, $payload);
            $product = $this->productService()->findById($productId);
        } catch (Throwable $throwable) {
            $response->json([
                'success' => false,
                'message' => 'Product could not be updated.',
            ], 500);
            return;
        }

        $response->json([
            'success' => true,
            'data' => $this->formatProduct($product ?? []),
            'message' => 'Product updated successfully.',
        ]);
    }

    public function destroy(Request $request, Response $response): void
    {
        $productId = (int) $request->route('id', 0);
        $existing = $this->productService()->findById($productId);

        if ($existing === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        try {
            $this->productService()->delete($productId);
        } catch (Throwable) {
            $response->json([
                'success' => false,
                'message' => 'Product could not be deleted.',
            ], 500);
            return;
        }

        $response->json([
            'success' => true,
            'data' => [
                'id' => $productId,
            ],
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function purchase(Request $request, Response $response): void
    {
        $productId = (int) $request->route('id', 0);

        if ($this->productService()->findById($productId) === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        $quantity = trim((string) $request->input('quantity', ''));

        if ($quantity === '' || filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity < 1) {
            $this->validationFailed($response, [
                'quantity' => 'Quantity must be an integer greater than or equal to 1.',
            ]);
            return;
        }


        try {
            $purchase = $this->purchaseService()->purchase(
                (int) $request->attribute('auth.user_id', 0),
                $productId,
                (int) $quantity
            );
        } catch (DomainException $exception) {
            $response->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
            return;
        }

        $response->json([
            'success' => true,
            'data' => $this->formatPurchase($purchase),
            'message' => 'Purchase completed successfully.',
        ], 201);
    }

    private function formatProduct(array $product): array
    {
        if (array_key_exists('price', $product)) {
            $product['price'] = CurrencyFormatter::formatUsd((string) $product['price']);
        }

        return $product;
    }

    private function formatPurchase(array $purchase): array
    {
        if (array_key_exists('unit_price', $purchase)) {
            $purchase['unit_price'] = CurrencyFormatter::formatUsd((string) $purchase['unit_price']);
        }

        if (array_key_exists('total_amount', $purchase)) {
            $purchase['total_amount'] = CurrencyFormatter::formatUsd((string) $purchase['total_amount']);
        }

        return $purchase;
    }

    private function productService(): ProductService
    {
        return new ProductService(new ProductRepository($this->database()));
    }

    private function purchaseService(): PurchaseService
    {
        $database = $this->database();

        return new PurchaseService(
            $database,
            new ProductRepository($database),
            new TransactionRepository($database)
        );
    }

    private function database(): Database
    {
        return new Database(require config_path('database.php'));
    }

    private function productPayload(Request $request): array
    {
        return [
            'name' => trim((string) $request->input('name', '')),
            'price' => trim((string) $request->input('price', '')),
            'quantity_available' => trim((string) $request->input('quantity_available', '')),
        ];
    }

    private function validateProductData(array $data): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = 'Product name is required.';
        }

        if ($data['price'] === '') {
            $errors['price'] = 'Price is required.';
        } elseif (!is_numeric($data['price']) || (float) $data['price'] <= 0) {
            $errors['price'] = 'Price must be a number greater than 0.';
        }

        if ($data['quantity_available'] === '') {
            $errors['quantity_available'] = 'Quantity available is required.';
        } elseif (filter_var($data['quantity_available'], FILTER_VALIDATE_INT) === false || (int) $data['quantity_available'] < 0) {
            $errors['quantity_available'] = 'Quantity available must be a non-negative integer.';
        }

        return $errors;
    }

    private function validationFailed(Response $response, array $errors): void
    {
        $response->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $errors,
        ], 422);
    }

    private function notFound(Response $response, string $message): void
    {
        $response->json([
            'success' => false,
            'message' => $message,
        ], 404);
    }
}