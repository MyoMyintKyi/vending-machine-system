<?php

declare(strict_types=1);

[$router, $request, $response] = require dirname(__DIR__) . '/bootstrap/app.php';

$router->dispatch($request, $response);