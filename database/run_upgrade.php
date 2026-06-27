<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$pdo = Database::connection();
$columns = [
    'smtp_host' => "VARCHAR(200) NULL",
    'smtp_port' => "SMALLINT NOT NULL DEFAULT 587",
    'smtp_user' => "VARCHAR(180) NULL",
    'smtp_pass' => "VARCHAR(255) NULL",
    'smtp_encryption' => "VARCHAR(10) NULL DEFAULT 'tls'",
    'backup_token' => "VARCHAR(64) NULL",
    'notify_payment_overdue' => "TINYINT(1) NOT NULL DEFAULT 1",
    'notify_deadline' => "TINYINT(1) NOT NULL DEFAULT 1",
    'notify_task_assigned' => "TINYINT(1) NOT NULL DEFAULT 1",
];

$existing = $pdo->query("SHOW COLUMNS FROM company_settings")->fetchAll(PDO::FETCH_COLUMN);
foreach ($columns as $col => $def) {
    if (!in_array($col, $existing, true)) {
        $pdo->exec("ALTER TABLE company_settings ADD COLUMN {$col} {$def}");
        echo "Added column: {$col}\n";
    }
}
echo "Upgrade complete.\n";
