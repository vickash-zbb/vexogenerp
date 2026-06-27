<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

session_name(config('app.session_name'));
session_start();

$router = require APP_PATH . '/routes.php';
$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
