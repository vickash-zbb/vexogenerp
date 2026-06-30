<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Vexogen ERP</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="<?= asset('images/vexogen-logo.png') ?>">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<style>
  body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--bg); }
  .login-wrap { width:400px; max-width:92vw; }
  .login-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:36px; box-shadow:var(--shadow-md); }
  .login-brand { display:flex; align-items:center; gap:12px; margin-bottom:28px; }
  .login-brand .brand-icon { width:48px; height:48px; }
  .login-title { font-size:22px; font-weight:600; margin-bottom:6px; }
  .login-sub { font-size:13px; color:var(--text-muted); margin-bottom:24px; }
  .login-error { background:var(--danger-light); color:var(--danger); padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:16px; }
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-brand">
      <div class="brand-icon login-logo"><img src="<?= asset('images/vexogen-logo.png') ?>" alt="Vexogen"></div>
      <div>
        <div style="font-size:18px;font-weight:600">Vexogen</div>
        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em">Agency ERP & CRM</div>
      </div>
    </div>
    <div class="login-title">Welcome back</div>
    <div class="login-sub">Sign in to manage your agency operations</div>
    <?php if ($err = flash('error')): ?><div class="login-error"><?= e($err) ?></div><?php endif; ?>
    <form method="post" action="<?= url('login') ?>">
      <?= \App\Core\CSRF::field() ?>
      <div class="form-group" style="margin-bottom:14px">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" value="<?= old('email') ?>" autocomplete="username" required autofocus>
      </div>
      <div class="form-group" style="margin-bottom:20px">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px">Sign In</button>
    </form>
  </div>
</div>
</body>
</html>
