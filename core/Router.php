<?php

declare(strict_types=1);

namespace Core;

use App\Middleware\MiddlewareInterface;
use Closure;

final class Router
{
    /** @var array<string, array<int, array{path:string, handler:callable|array{0:class-string,1:string}, middleware:list<class-string|array<int, mixed>>}>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function dispatch(\Core\Request $request, \Core\Response $response): void
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            $params = $this->match($route['path'], $path);

            if ($params === null) {
                continue;
            }

            $request->setRouteParams($params);

            foreach ($route['middleware'] as $middlewareDefinition) {
                $middleware = $this->resolveMiddleware($middlewareDefinition);

                if (!$middleware->handle($request, $response)) {
                    return;
                }
            }

            $handler = $route['handler'];

            if ($handler instanceof Closure || is_callable($handler)) {
                $handler($request, $response);
                return;
            }

            if (is_array($handler) && isset($handler[0], $handler[1])) {
                $controller = new $handler[0]();
                $controller->{$handler[1]}($request, $response);
                return;
            }
        }

        $response->json([
            'success' => false,
            'message' => 'Route not found.',
        ], 404);
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $normalizedPath = rtrim($path, '/') ?: '/';

        $this->routes[$method][] = [
            'path' => $normalizedPath,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    private function resolveMiddleware(string|array $middlewareDefinition): MiddlewareInterface
    {
        if (is_string($middlewareDefinition)) {
            return new $middlewareDefinition();
        }

        $className = array_shift($middlewareDefinition);

        if (!is_string($className)) {
            throw new \RuntimeException('Invalid middleware definition.');
        }

        return new $className(...$middlewareDefinition);
    }

    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);

        if ($pattern === null) {
            return null;
        }

        $regex = '#^' . $pattern . '$#';
        $matched = preg_match($regex, $requestPath, $matches);

        if ($matched !== 1) {
            return null;
        }

        $params = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}