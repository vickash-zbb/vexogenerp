<?php

declare(strict_types=1);

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if (!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<h1>Application startup failed</h1>';
    echo '<p>' . htmlspecialchars($error['message']) . '</p>';
    echo '<p><code>' . htmlspecialchars($error['file']) . ':' . $error['line'] . '</code></p>';
    echo '<p>If this mentions <code>vendor/</code>, delete the broken <code>vendor</code> folder on the server or upload a complete one from <code>composer install</code>.</p>';
});

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>PHP 8.0+ required</h1><p>This app needs PHP 8.0 or higher. Your server is running PHP '
        . htmlspecialchars(PHP_VERSION) . '. In Hostinger hPanel, set PHP to 8.1 or 8.2.</p>';
    exit;
}

define('BASE_PATH', defined('VEXOGEN_BASE_PATH') ? VEXOGEN_BASE_PATH : dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('PUBLIC_PATH', defined('VEXOGEN_PUBLIC_PATH') ? VEXOGEN_PUBLIC_PATH : BASE_PATH . '/public');

date_default_timezone_set('Asia/Kolkata');

foreach (['uploads', 'backups', 'logs'] as $dir) {
    $path = STORAGE_PATH . '/' . $dir;
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}

$signatures = PUBLIC_PATH . '/uploads/signatures';
if (!is_dir($signatures)) {
    @mkdir($signatures, 0755, true);
}

$envFile = BASE_PATH . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, "\"'");

        if ($key !== '' && getenv($key) === false && !isset($_ENV[$key]) && !isset($_SERVER[$key])) {
            @putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = APP_PATH . '/' . $relative . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

require_once APP_PATH . '/helpers.php';

\App\Core\ErrorHandler::register();
