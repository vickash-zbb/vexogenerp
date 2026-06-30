<?php

$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$host = explode(':', $host)[0];

if ($host === 'erp.vexogen.in') {
    return [
        'host'     => 'localhost',
        'port'     => '3306',
        'database' => 'u899224075_erpvexogen',
        'username' => 'u899224075_erpvexogen',
        'password' => env('LIVE_DB_PASS', env('DB_PASS', '')),
        'charset'  => 'utf8mb4',
    ];
}

return [
    'host'     => 'localhost',
    'port'     => '3306',
    'database' => 'vexogen_crm',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];
