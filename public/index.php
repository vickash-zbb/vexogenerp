<?php

declare(strict_types=1);

$bootstrapPaths = [
    __DIR__ . '/../app/bootstrap.php',
    __DIR__ . '/app/bootstrap.php',
];

$loaded = false;
foreach ($bootstrapPaths as $path) {
    if (is_file($path)) {
        require_once $path;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Application not found</h1>'
        . '<p>Upload the full Vexogen CRM project. The <code>app/</code> folder must sit next to <code>public/</code>.</p>'
        . '<p>On Hostinger: upload all folders to <code>public_html</code> or set the subdomain document root to the <code>public</code> folder.</p>';
    exit;
}

session_name(config('app.session_name'));
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

try {
    $router = require APP_PATH . '/routes.php';
    $router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
} catch (Throwable $e) {
    \App\Core\ErrorHandler::handleException($e);
}
