<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Core\Database;
use Core\Request;
use Core\Response;
use Throwable;

final class ProductsController
{
    public function __construct(
        private readonly ?ProductService $productService = null
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $sort = (string) $request->query('sort', 'name');
        $direction = strtolower((string) $request->query('direction', 'asc'));
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $response->view('products/index', [
            'title' => 'Products',
            'flash' => (string) $request->pullSessionValue('flash', ''),
            'products' => $this->service()->findAll($page, 10, $sort, $direction),
            'sort' => $sort,
            'direction' => $direction,
            'role' => (string) $request->session('role', ''),
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

        $request->setSessionValue('flash', 'Purchase workflow will be completed in Phase 9.');
        $response->redirect('/products/' . $productId . '/purchase');
    }

    private function service(): ProductService
    {
        if ($this->productService instanceof ProductService) {
            return $this->productService;
        }

        $database = new Database(require config_path('database.php'));
        $repository = new ProductRepository($database);

        return new ProductService($repository);
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