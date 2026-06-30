<?php

declare(strict_types=1);

function vexogen_candidate_roots(): array
{
    $roots = [
        dirname(__DIR__),
        __DIR__,
        dirname(__DIR__, 2),
        __DIR__ . '/vexogen-crm',
        __DIR__ . '/vexogen crm',
        dirname(__DIR__) . '/vexogen-crm',
        dirname(__DIR__) . '/vexogen crm',
    ];

    foreach ([__DIR__, dirname(__DIR__)] as $scanRoot) {
        foreach (glob($scanRoot . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $roots[] = $dir;
        }
    }

    return array_values(array_unique($roots));
}

$bootstrapPath = null;
foreach (vexogen_candidate_roots() as $root) {
    $path = rtrim($root, '/\\') . '/app/bootstrap.php';
    if (is_file($path)) {
        $bootstrapPath = $path;
        define('VEXOGEN_BASE_PATH', rtrim($root, '/\\'));
        define('VEXOGEN_PUBLIC_PATH', is_dir(rtrim($root, '/\\') . '/public') ? rtrim($root, '/\\') . '/public' : __DIR__);
        break;
    }
}

$loaded = false;
$bootstrapError = null;
if ($bootstrapPath) {
    try {
        require_once $bootstrapPath;
        $loaded = true;
    } catch (Throwable $e) {
        $bootstrapError = $e;
    }
}

if (!$loaded) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Application not found</h1>'
        . '<p>Upload the full Vexogen CRM project. The <code>app/</code>, <code>config/</code>, <code>storage/</code>, and <code>vendor/</code> folders must be uploaded together.</p>'
        . '<p>On Hostinger: extract the deployment ZIP directly inside <code>public_html</code>, or set the subdomain document root to the project <code>public</code> folder.</p>';
    exit;
}

if ($bootstrapError) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Application startup failed</h1>'
        . '<p>' . htmlspecialchars($bootstrapError->getMessage()) . '</p>'
        . '<p>Check <code>storage/logs/php-errors.log</code> on the server.</p>';
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
