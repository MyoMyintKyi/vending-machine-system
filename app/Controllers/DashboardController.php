<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Request;
use Core\Response;

final class DashboardController
{
    public function index(Request $request, Response $response): void
    {
        $response->view('auth/dashboard', [
            'title' => 'Dashboard',
            'flash' => (string) $request->pullSessionValue('flash', ''),
            'username' => (string) $request->session('username', ''),
            'email' => (string) $request->session('email', ''),
            'role' => (string) $request->session('role', ''),
        ]);
    }
}