<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, Response $response): bool;
}