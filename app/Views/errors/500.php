<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Server Error — Vexogen ERP</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
  body { font-family: Inter, sans-serif; background: #F8FAFC; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; color: #0F172A; }
  .box { background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 32px; width: 520px; max-width: 92vw; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.07); }
  h1 { font-size: 22px; margin: 0 0 8px; }
  p { color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 12px; }
  code { background: #F1F5F9; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
  ul { margin: 12px 0; padding-left: 20px; color: #475569; font-size: 14px; }
  a { color: #0F62FE; }
  .detail { background: #FEF2F2; color: #991B1B; padding: 10px 12px; border-radius: 8px; font-size: 12px; margin-top: 16px; word-break: break-word; }
</style>
</head>
<body>
<div class="box">
  <h1>Something went wrong</h1>
  <p>The application hit a server error. Common fixes on Hostinger:</p>
  <ul>
    <li>Set PHP to <strong>8.0+</strong> in hPanel → Advanced → PHP Configuration</li>
    <li>Create <code>.env</code> with <code>APP_URL</code> and database credentials</li>
    <li>Upload the full project (<code>app/</code>, <code>config/</code>, <code>public/</code>, <code>vendor/</code>)</li>
    <li>Import <code>database/schema.sql</code> or run <a href="<?= htmlspecialchars(detect_app_url() . '/install.php') ?>">install.php</a></li>
  </ul>
  <p>Details are logged in <code>storage/logs/php-errors.log</code> on your server.</p>
  <?php if (!empty($errorMessage)): ?>
  <div class="detail"><?= htmlspecialchars($errorMessage) ?></div>
  <?php endif; ?>
  <p style="margin-top:20px"><a href="<?= htmlspecialchars(detect_app_url() . '/login') ?>">Try login page</a></p>
</div>
</body>
</html>
