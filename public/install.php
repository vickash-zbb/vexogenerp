<?php

declare(strict_types=1);

$installer = dirname(__DIR__) . '/install.php';
if (!is_file($installer)) {
    http_response_code(500);
    echo 'Installer not found. Upload install.php to the project root.';
    exit;
}

require $installer;
