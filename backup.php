<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

use App\Services\BackupService;

$token = $_GET['token'] ?? $_SERVER['HTTP_X_BACKUP_TOKEN'] ?? '';
if (!BackupService::verifyToken($token)) {
    http_response_code(403);
    die('Invalid backup token');
}

try {
    $result = BackupService::create();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'backup' => $result]);
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
