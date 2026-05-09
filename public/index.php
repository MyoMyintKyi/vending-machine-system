<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
	$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

	if (is_string($requestPath) && str_starts_with($requestPath, '/assets/')) {
		$assetFile = __DIR__ . $requestPath;

		if (is_file($assetFile)) {
			$extension = strtolower(pathinfo($assetFile, PATHINFO_EXTENSION));
			$contentTypes = [
				'css' => 'text/css; charset=utf-8',
				'js' => 'application/javascript; charset=utf-8',
				'png' => 'image/png',
				'jpg' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif' => 'image/gif',
				'svg' => 'image/svg+xml',
				'webp' => 'image/webp',
				'ico' => 'image/x-icon',
			];

			header('Content-Type: ' . ($contentTypes[$extension] ?? 'application/octet-stream'));
			readfile($assetFile);
			return;
		}
	}
}

[$router, $request, $response] = require dirname(__DIR__) . '/bootstrap/app.php';

$router->dispatch($request, $response);