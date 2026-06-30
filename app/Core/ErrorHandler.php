<?php

declare(strict_types=1);

namespace App\Core;

use PDOException;
use Throwable;

class ErrorHandler
{
    public static function register(): void
    {
        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        ini_set('error_log', $logDir . '/php-errors.log');

        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException(Throwable $e): void
    {
        self::log($e);
        self::respond($e);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            self::logMessage($error['message'], $error['file'], $error['line']);
            if (!headers_sent()) {
                self::respond(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
            }
        }
    }

    public static function log(Throwable $e): void
    {
        self::logMessage($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    }

    private static function logMessage(string $message, string $file, int $line, ?string $trace = null): void
    {
        $logFile = STORAGE_PATH . '/logs/php-errors.log';
        $entry = date('Y-m-d H:i:s') . " [{$file}:{$line}] {$message}";
        if ($trace) {
            $entry .= "\n" . $trace;
        }
        $entry .= "\n";
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    private static function respond(Throwable $e): void
    {
        if (headers_sent()) {
            echo "\nApplication error. Check storage/logs/php-errors.log on the server.";
            return;
        }

        $isDb = $e instanceof PDOException
            || str_contains($e->getMessage(), 'Database connection failed');

        if (self::isApiRequest()) {
            json_response([
                'success' => false,
                'message' => $isDb ? 'Database connection failed. Check LIVE_DB_PASS.' : 'Server error.',
            ], 500);
        }

        http_response_code(500);
        if ($isDb && is_file(APP_PATH . '/Views/errors/setup.php')) {
            $hint = $e->getMessage();
            require APP_PATH . '/Views/errors/setup.php';
            return;
        }

        if (is_file(APP_PATH . '/Views/errors/500.php')) {
            $errorMessage = $e->getMessage();
            require APP_PATH . '/Views/errors/500.php';
            return;
        }

        echo 'Application error. Check storage/logs/php-errors.log on the server.';
    }

    private static function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_starts_with($uri, '/api/')
            || str_contains($uri, '/api/')
            || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }
}
