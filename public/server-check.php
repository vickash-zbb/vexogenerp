<?php

declare(strict_types=1);

$bootstrapError = null;

try {
    require_once __DIR__ . '/../app/bootstrap.php';
} catch (Throwable $e) {
    $bootstrapError = $e;
}

header('Content-Type: text/html; charset=utf-8');

function check_status(bool $ok): string
{
    return $ok ? 'OK' : 'FAIL';
}

function check_row(string $label, bool $ok, string $detail = ''): void
{
    $class = $ok ? 'ok' : 'fail';
    echo '<tr><th>' . htmlspecialchars($label) . '</th><td class="' . $class . '">'
        . check_status($ok) . '</td><td>' . htmlspecialchars($detail) . '</td></tr>';
}

$dbStatus = false;
$dbDetail = 'Not checked';

if (!$bootstrapError && class_exists(\App\Core\Database::class)) {
    try {
        $pdo = \App\Core\Database::connection();
        $dbStatus = (bool) $pdo->query('SELECT 1')->fetchColumn();
        $dbNameVal = function_exists('env') ? (env('DB_NAME') ?: '(no DB_NAME)') : '(env helper missing)';
        $dbDetail = 'Connected to ' . $dbNameVal;
    } catch (Throwable $e) {
        $dbDetail = $e->getMessage();
    }
}

$appUrl = function_exists('env') ? (env('APP_URL') ?: 'auto-detect') : '(env helper missing)';
$dbHost = function_exists('env') ? (env('DB_HOST') ?: '(not set)') : '(env helper missing)';
$dbName = function_exists('env') ? (env('DB_NAME') ?: '(not set)') : '(env helper missing)';
$dbUser = function_exists('env') ? (env('DB_USER') ?: '(not set)') : '(env helper missing)';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vexogen Server Check</title>
<style>
body { font-family: Arial, sans-serif; background:#f8fafc; color:#0f172a; margin:0; padding:32px; }
.wrap { max-width: 920px; margin: 0 auto; background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:24px; }
h1 { margin:0 0 8px; font-size:24px; }
p { color:#475569; line-height:1.5; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { border-top:1px solid #e2e8f0; padding:12px; text-align:left; vertical-align:top; font-size:14px; }
th { width:230px; }
.ok { color:#15803d; font-weight:700; }
.fail { color:#b91c1c; font-weight:700; }
code { background:#f1f5f9; padding:2px 5px; border-radius:4px; }
</style>
</head>
<body>
<div class="wrap">
  <h1>Vexogen Server Check</h1>
  <p>Use this page during setup only. Delete <code>public/server-check.php</code> after the site is working.</p>
  <table>
    <?php check_row('Bootstrap', !$bootstrapError, $bootstrapError ? $bootstrapError->getMessage() : 'Loaded'); ?>
    <?php check_row('PHP version', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION); ?>
    <?php check_row('PDO MySQL extension', extension_loaded('pdo_mysql'), 'Required for database'); ?>
    <?php check_row('GD extension', extension_loaded('gd'), 'Recommended for PDFs with images/logos'); ?>
    <?php check_row('ZIP extension', extension_loaded('zip'), 'Recommended for Composer packages'); ?>
    <?php check_row('APP_URL', true, $appUrl); ?>
    <?php check_row('DB_HOST', $dbHost !== '(not set)', $dbHost); ?>
    <?php check_row('DB_NAME', $dbName !== '(not set)', $dbName); ?>
    <?php check_row('DB_USER', $dbUser !== '(not set)', $dbUser); ?>
    <?php check_row('Vendor autoload', is_file(BASE_PATH . '/vendor/autoload.php'), 'vendor/autoload.php'); ?>
    <?php check_row('Composer ready', function_exists('load_composer') && load_composer(), 'Dompdf/PHPMailer'); ?>
    <?php check_row('Storage writable', is_writable(STORAGE_PATH), STORAGE_PATH); ?>
    <?php check_row('Logs writable', is_writable(STORAGE_PATH . '/logs'), STORAGE_PATH . '/logs'); ?>
    <?php check_row('Database connection', $dbStatus, $dbDetail); ?>
  </table>
</div>
</body>
</html>
