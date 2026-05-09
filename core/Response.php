<?php

declare(strict_types=1);

namespace Core;

final class Response
{
    public function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function redirect(string $location, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header('Location: ' . $location);
    }

    public function view(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewFile = base_path('views/' . $view . '.php');
        $layoutFile = base_path('views/' . $layout . '.php');

        if (!file_exists($viewFile)) {
            throw new \RuntimeException(sprintf('View not found: %s', $view));
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = (string) ob_get_clean();

        if (file_exists($layoutFile)) {
            require $layoutFile;
            return;
        }

        echo $content;
    }
}