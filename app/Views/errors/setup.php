<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Database Setup — Vexogen ERP</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
  body { font-family: Inter, sans-serif; background: #F8FAFC; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
  .box { background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 32px; width: 520px; max-width: 92vw; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.07); }
  h1 { font-size: 22px; margin: 0 0 8px; }
  p, li { color: #475569; font-size: 14px; line-height: 1.6; }
  code { background: #F1F5F9; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
  .error { background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 8px; font-size: 13px; margin: 16px 0; word-break: break-word; }
  a { color: #0F62FE; }
</style>
</head>
<body>
<div class="box">
  <h1>Database connection failed</h1>
  <p>Vexogen ERP cannot connect to MySQL. Update your <code>.env</code> file on the server:</p>
  <pre style="background:#F8FAFC;padding:14px;border-radius:8px;font-size:12px;line-height:1.5;margin:16px 0">APP_URL=https://erp.vexogen.in
DB_HOST=localhost
DB_NAME=your_hostinger_db_name
DB_USER=your_hostinger_db_user
DB_PASS=your_hostinger_db_password</pre>
  <p>Then import the database via phpMyAdmin or open <a href="<?= htmlspecialchars(detect_app_url() . '/install.php') ?>">install.php</a> once.</p>
  <?php if (!empty($hint)): ?>
  <div class="error"><?= htmlspecialchars($hint) ?></div>
  <?php endif; ?>
</div>
</body>
</html>
