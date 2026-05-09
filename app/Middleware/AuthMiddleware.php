<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Middleware\MiddlewareInterface;
use Core\Request;
use Core\Response;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response): bool
    {
        if ((bool) $request->session('authenticated', false) === true) {
            return true;
        }

        $request->setSessionValue('flash', 'Please log in to continue.');
        $response->redirect('/login');

        return false;
    }
}