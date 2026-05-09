<?php

declare(strict_types=1);

namespace Core;

final class Request
{
    public function __construct(
        private readonly array $get,
        private readonly array $post,
        private readonly array $server,
        private readonly array $cookies,
        private readonly array $files,
        private array &$session,
        private array $routeParams = []
    ) {
    }

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES, $_SESSION);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        return rtrim($path, '/') ?: '/';
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

        return $this->server[$normalized] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function routeParams(): array
    {
        return $this->routeParams;
    }

    public function session(string $key, mixed $default = null): mixed
    {
        return $this->session[$key] ?? $default;
    }

    public function pullSessionValue(string $key, mixed $default = null): mixed
    {
        $value = $this->session[$key] ?? $default;
        unset($this->session[$key], $_SESSION[$key]);

        return $value;
    }

    public function setSessionValue(string $key, mixed $value): void
    {
        $this->session[$key] = $value;
        $_SESSION[$key] = $value;
    }

    public function unsetSessionValue(string $key): void
    {
        unset($this->session[$key], $_SESSION[$key]);
    }

    public function invalidateSession(): void
    {
        $this->session = [];
        $_SESSION = [];
    }
}