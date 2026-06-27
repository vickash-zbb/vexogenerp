<?php

declare(strict_types=1);

/**
 * Vexogen CRM Installer
 * Run once: http://localhost/vexogen%20crm/install.php
 * Delete this file after successful installation.
 */

require_once __DIR__ . '/app/bootstrap.php';

$step = $_GET['step'] ?? 'check';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'install') {
    try {
        $cfg = require CONFIG_PATH . '/database.php';
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['port'],
                $cfg['database'],
                $cfg['charset']
            ),
            $cfg['username'],
            $cfg['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        foreach (array_filter(array_map('trim', explode(';', $schema))) as $sql) {
            if ($sql === '') {
                continue;
            }
            $upper = strtoupper($sql);
            if (str_starts_with($upper, 'CREATE DATABASE') || str_starts_with($upper, 'USE ')) {
                continue;
            }
            $pdo->exec($sql);
        }

        // Set admin password to admin123
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("DELETE FROM users");
        
        $seed = file_get_contents(__DIR__ . '/database/seed.sql');
        foreach (array_filter(array_map('trim', explode(';', $seed))) as $sql) {
            if ($sql !== '' && !str_starts_with(strtoupper($sql), 'USE ')) {
                $pdo->exec($sql);
            }
        }
        $pdo->prepare('UPDATE users SET password = ? WHERE email = ?')->execute([$hash, 'admin@vexogen.com']);

        // Create storage dirs
        foreach (['uploads', 'backups', 'logs'] as $dir) {
            $path = STORAGE_PATH . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Run upgrade for any new columns
        if (is_file(__DIR__ . '/database/run_upgrade.php')) {
            ob_start();
            include __DIR__ . '/database/run_upgrade.php';
            ob_end_clean();
        }

        $message = 'Installation complete! Login with admin@vexogen.com / admin123';
        $step = 'done';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Install Vexogen CRM</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
  body { font-family: Inter, sans-serif; background: #F8FAFC; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
  .box { background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 36px; width: 480px; max-width: 92vw; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.07); }
  h1 { font-size: 22px; margin: 0 0 8px; }
  p { color: #475569; font-size: 14px; line-height: 1.6; }
  .btn { background: #0F62FE; color: #fff; border: none; padding: 11px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; margin-top: 20px; }
  .error { background: #FEE2E2; color: #DC2626; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
  .success { background: #DCFCE7; color: #16A34A; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
  a { color: #0F62FE; }
</style>
</head>
<body>
<div class="box">
  <h1>Vexogen CRM Installer</h1>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($message): ?><div class="success"><?= htmlspecialchars($message) ?></div><p><a href="<?= htmlspecialchars(detect_app_url()) ?>">Go to Application →</a></p><?php endif; ?>
  <?php if ($step !== 'done'): ?>
  <p>This will create tables and sample data in your Hostinger MySQL database using credentials from <code>.env</code>.</p>
  <p>Requirements: PHP 8.0+, MySQL database created in hPanel, and <code>.env</code> configured with <code>DB_HOST</code>, <code>DB_NAME</code>, <code>DB_USER</code>, <code>DB_PASS</code>.</p>
  <form method="post">
    <input type="hidden" name="action" value="install">
    <button type="submit" class="btn">Install Database</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
