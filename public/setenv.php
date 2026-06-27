<?php
/**
 * Vexogen Auto-Setup
 * 1. Upload this file to: public_html/public/setenv.php
 * 2. Open: https://erp.vexogen.in/setenv.php
 * 3. Done! Delete this file after.
 */
declare(strict_types=1);

$basePath = dirname(__DIR__);
$envPath  = $basePath . '/.env';

// ── Write .env ────────────────────────────────────────────────────────────────
$envContent = "APP_URL=https://erp.vexogen.in\n"
    . "\n"
    . "DB_HOST=localhost\n"
    . "DB_PORT=3306\n"
    . "DB_NAME=u899224075_erpvexogen\n"
    . "DB_USER=u899224075_erpvexogen\n"
    . "DB_PASS=Erp\@vexogen1\n"
    . "\n"
    . "MAIL_DRIVER=smtp\n"
    . "MAIL_HOST=smtp.hostinger.com\n"
    . "MAIL_PORT=465\n"
    . "MAIL_USERNAME=hello\@vexogen.in\n"
    . "MAIL_PASSWORD=your-real-mail-password\n"
    . "MAIL_ENCRYPTION=ssl\n"
    . "MAIL_FROM=hello\@vexogen.in\n"
    . "MAIL_FROM_NAME=Vexogen\n";

$envOk  = (@file_put_contents($envPath, $envContent) !== false);

// ── Run database installer ────────────────────────────────────────────────────
$dbOk  = false;
$dbMsg = '';
if ($envOk) {
    try {
        $pdo = new PDO(
            'mysql:host=localhost;port=3306;dbname=u899224075_erpvexogen;charset=utf8mb4',
            'u899224075_erpvexogen',
            'Erp@vexogen1',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Run schema
        $schema = @file_get_contents($basePath . '/database/schema.sql');
        if ($schema) {
            foreach (array_filter(array_map('trim', explode(';', $schema))) as $sql) {
                if ($sql === '') continue;
                $upper = strtoupper($sql);
                if (str_starts_with($upper, 'CREATE DATABASE') || str_starts_with($upper, 'USE ')) continue;
                $pdo->exec($sql);
            }
        }

        // Run seed
        $seed = @file_get_contents($basePath . '/database/seed.sql');
        if ($seed) {
            foreach (array_filter(array_map('trim', explode(';', $seed))) as $sql) {
                if ($sql !== '' && !str_starts_with(strtoupper($sql), 'USE ')) {
                    $pdo->exec($sql);
                }
            }
        }

        // Set admin password
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password = ? WHERE email = ?')
            ->execute([$hash, 'admin@vexogen.com']);

        // Create storage dirs
        foreach (['uploads','backups','logs'] as $d) {
            $p = $basePath . '/storage/' . $d;
            if (!is_dir($p)) @mkdir($p, 0755, true);
        }

        $dbOk  = true;
        $dbMsg = 'Database tables created. Admin password set.';

    } catch (Throwable $e) {
        $dbMsg = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Vexogen Setup</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Outfit',sans-serif;background:#0b0f19;color:#f3f4f6;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
  .card{background:rgba(22,30,49,.9);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:36px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,.5)}
  h1{font-size:24px;font-weight:700;margin-bottom:6px}
  .sub{color:#9ca3af;font-size:14px;margin-bottom:28px}
  .step{display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid rgba(255,255,255,.06)}
  .step:last-child{border:none}
  .icon{font-size:22px;flex-shrink:0;margin-top:2px}
  .step-title{font-weight:600;font-size:15px;margin-bottom:4px}
  .step-detail{font-size:13px;color:#9ca3af}
  .ok .step-title{color:#10b981}
  .fail .step-title{color:#ef4444}
  .btn{display:block;width:100%;padding:14px;border-radius:12px;background:#3b82f6;color:#fff;font-size:16px;font-weight:700;text-align:center;text-decoration:none;margin-top:28px;border:none;cursor:pointer;box-shadow:0 4px 20px rgba(59,130,246,.4);transition:.2s}
  .btn:hover{background:#2563eb}
  code{background:rgba(255,255,255,.08);padding:2px 6px;border-radius:4px;font-family:monospace;font-size:12px}
</style>
</head>
<body>
<div class="card">
  <h1>🚀 Vexogen Setup</h1>
  <p class="sub">Auto-configuring your live server...</p>

  <div class="step <?= $envOk ? 'ok' : 'fail' ?>">
    <div class="icon"><?= $envOk ? '✅' : '❌' ?></div>
    <div>
      <div class="step-title"><?= $envOk ? '.env file written' : '.env write failed' ?></div>
      <div class="step-detail">
        <?php if ($envOk): ?>
          DB_NAME = <code>u899224075_erpvexogen</code> saved to server
        <?php else: ?>
          Check folder permissions on <code>public_html/</code>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="step <?= $dbOk ? 'ok' : 'fail' ?>">
    <div class="icon"><?= $dbOk ? '✅' : '❌' ?></div>
    <div>
      <div class="step-title"><?= $dbOk ? 'Database ready' : 'Database error' ?></div>
      <div class="step-detail"><?= htmlspecialchars($dbMsg ?: ($envOk ? 'Could not connect' : 'Skipped — fix .env first')) ?></div>
    </div>
  </div>

  <?php if ($envOk && $dbOk): ?>
  <a href="https://erp.vexogen.in" class="btn">🎉 Open Vexogen ERP →</a>
  <p style="text-align:center;font-size:12px;color:#6b7280;margin-top:12px">
    Login: <code>admin@vexogen.com</code> / <code>admin123</code><br>
    ⚠️ Delete <code>public/setenv.php</code> after logging in!
  </p>
  <?php elseif ($envOk): ?>
  <a href="https://erp.vexogen.in/install.php" class="btn" style="background:#f59e0b">⚠️ DB error — Try Manual Install →</a>
  <?php else: ?>
  <p style="color:#ef4444;text-align:center;margin-top:20px;font-size:14px">Setup failed. Check Hostinger file permissions.</p>
  <?php endif; ?>
</div>
</body>
</html>
