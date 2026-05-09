<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Request;
use Core\Response;

final class TestController
{
    public function index(Request $request, Response $response): void
    {
        $response->view('test', [
            'title' => 'Test Route',
            'message' => 'The test route, controller, and view are wired correctly.',
            'path' => $request->path(),
            'method' => $request->method(),
        ]);
    }

    public function authenticated(Request $request, Response $response): void
    {
        $response->view('test', [
            'title' => 'Protected Test Route',
            'message' => 'You are authenticated and the auth middleware allowed this request.',
            'path' => $request->path(),
            'method' => $request->method(),
            'username' => (string) $request->session('username', ''),
            'role' => (string) $request->session('role', ''),
        ]);
    }

    public function adminOnly(Request $request, Response $response): void
    {
        $response->view('test', [
            'title' => 'Admin-Only Test Route',
            'message' => 'You reached an Admin-only route protected by the role middleware.',
            'path' => $request->path(),
            'method' => $request->method(),
            'username' => (string) $request->session('username', ''),
            'role' => (string) $request->session('role', ''),
        ]);
    }
}