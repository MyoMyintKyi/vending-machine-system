<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Interfaces\ProductServiceInterface;
use App\Interfaces\PurchaseServiceInterface;
use App\Repositories\ProductRepository;
use App\Repositories\TransactionRepository;
use App\Services\ProductService;
use App\Services\PurchaseService;
use Core\Database;
use Core\Request;
use Core\Response;
use DomainException;
use Throwable;

final class ProductsController
{
    public function __construct(
        private readonly ?ProductServiceInterface $productService = null,
        private readonly ?PurchaseServiceInterface $purchaseService = null
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $sort = (string) $request->query('sort', 'name');
        $direction = strtolower((string) $request->query('direction', 'asc'));
        $direction = $direction === 'desc' ? 'desc' : 'asc';
        $totalProducts = $this->service()->countAll();
        $totalPages = max(1, (int) ceil($totalProducts / $perPage));
        $page = min($page, $totalPages);
        $products = $this->service()->findAll($page, $perPage, $sort, $direction);

        $response->view('products/index', [
            'title' => 'Products',
            'flash' => (string) $request->pullSessionValue('flash', ''),
            'products' => $products,
            'sort' => $sort,
            'direction' => $direction,
            'role' => (string) $request->session('role', ''),
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'hasPreviousPage' => $page > 1,
            'hasNextPage' => $page < $totalPages,
        ]);
    }

    public function show(Request $request, Response $response): void
    {
        $product = $this->service()->findById((int) $request->route('id', 0));

        if ($product === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        $response->view('products/show', [
            'title' => 'Product Details',
            'product' => $product,
            'role' => (string) $request->session('role', ''),
            'flash' => (string) $request->pullSessionValue('flash', ''),
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $response->view('products/create', [
            'title' => 'Create Product',
            'errors' => (array) $request->pullSessionValue('errors', []),
            'old' => (array) $request->pullSessionValue('old', []),
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $data = $this->productPayload($request);
        $errors = $this->validateProductData($data);

        if ($errors !== []) {
            $this->redirectWithFormState($request, $response, '/products/create', $errors, $data);
            return;
        }

        try {
            $productId = $this->service()->create($data);
        } catch (Throwable) {
            $this->redirectWithFormState($request, $response, '/products/create', [
                'form' => 'Product could not be created. Please verify the details and try again.',
            ], $data);
            return;
        }

        $request->setSessionValue('flash', 'Product created successfully.');
        $response->redirect('/products/' . $productId);
    }

    public function edit(Request $request, Response $response): void
    {
        $product = $this->service()->findById((int) $request->route('id', 0));

        if ($product === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        $response->view('products/edit', [
            'title' => 'Edit Product',
            'product' => $product,
            'errors' => (array) $request->pullSessionValue('errors', []),
            'old' => (array) $request->pullSessionValue('old', []),
        ]);
    }

    public function update(Request $request, Response $response): void
    {
        $productId = (int) $request->route('id', 0);
        $product = $this->service()->findById($productId);

        if ($product === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        $data = $this->productPayload($request);
        $errors = $this->validateProductData($data);

        if ($errors !== []) {
            $this->redirectWithFormState($request, $response, '/products/' . $productId . '/edit', $errors, $data);
            return;
        }

        try {
            $this->service()->update($productId, $data);
        } catch (Throwable) {
            $this->redirectWithFormState($request, $response, '/products/' . $productId . '/edit', [
                'form' => 'Product could not be updated. Please verify the details and try again.',
            ], $data);
            return;
        }

        $request->setSessionValue('flash', 'Product updated successfully.');
        $response->redirect('/products/' . $productId);
    }

    public function destroy(Request $request, Response $response): void
    {
        $productId = (int) $request->route('id', 0);
        $product = $this->service()->findById($productId);

        if ($product === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        try {
            $this->service()->delete($productId);
        } catch (Throwable) {
            $request->setSessionValue('flash', 'Product could not be deleted.');
            $response->redirect('/products/' . $productId);
            return;
        }

        $request->setSessionValue('flash', 'Product deleted successfully.');
        $response->redirect('/products');
    }

    public function purchaseForm(Request $request, Response $response): void
    {
        $product = $this->service()->findById((int) $request->route('id', 0));

        if ($product === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        $response->view('products/purchase', [
            'title' => 'Purchase Product',
            'product' => $product,
            'flash' => (string) $request->pullSessionValue('flash', ''),
            'errors' => (array) $request->pullSessionValue('errors', []),
            'old' => (array) $request->pullSessionValue('old', ['quantity' => '1']),
        ]);
    }

    public function purchase(Request $request, Response $response): void
    {
        $productId = (int) $request->route('id', 0);
        $product = $this->service()->findById($productId);

        if ($product === null) {
            $this->notFound($response, 'Product not found.');
            return;
        }

        $quantity = trim((string) $request->input('quantity', ''));
        $errors = $this->validatePurchaseData($quantity);

        if ($errors !== []) {
            $this->redirectWithFormState($request, $response, '/products/' . $productId . '/purchase', $errors, [
                'quantity' => $quantity,
            ]);
            return;
        }

        try {
            $purchase = $this->purchaseService()->purchase(
                (int) $request->session('user_id', 0),
                $productId,
                (int) $quantity
            );
        } catch (DomainException $exception) {
            $this->redirectWithFormState($request, $response, '/products/' . $productId . '/purchase', [
                'quantity' => $exception->getMessage(),
            ], [
                'quantity' => $quantity,
            ]);
            return;
        }

        $request->setSessionValue('flash', sprintf(
            'Purchase completed successfully. Quantity: %d. Total: %s. Available quantity is now %d.',
            $purchase['quantity'],
            $purchase['total_amount'],
            $purchase['quantity_available']
        ));
        $response->redirect('/products/' . $productId . '/purchase');
    }

    private function service(): ProductServiceInterface
    {
        if ($this->productService instanceof ProductServiceInterface) {
            return $this->productService;
        }

        $database = new Database(require config_path('database.php'));
        $repository = new ProductRepository($database);

        return new ProductService($repository);
    }

    private function purchaseService(): PurchaseServiceInterface
    {
        if ($this->purchaseService instanceof PurchaseServiceInterface) {
            return $this->purchaseService;
        }

        $database = new Database(require config_path('database.php'));
        $productRepository = new ProductRepository($database);
        $transactionRepository = new TransactionRepository($database);

        return new PurchaseService($database, $productRepository, $transactionRepository);
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

    private function validatePurchaseData(string $quantity): array
    {
        $errors = [];

        if ($quantity === '') {
            $errors['quantity'] = 'Quantity is required.';
        } elseif (filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity < 1) {
            $errors['quantity'] = 'Quantity must be an integer greater than or equal to 1.';
        }

        return $errors;
    }

    private function redirectWithFormState(Request $request, Response $response, string $path, array $errors, array $old): void
    {
        $request->setSessionValue('errors', $errors);
        $request->setSessionValue('old', $old);
        $response->redirect($path);
    }

    private function notFound(Response $response, string $message): void
    {
        $response->json([
            'success' => false,
            'message' => $message,
        ], 404);
    }
}