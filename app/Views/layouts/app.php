<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Dashboard') ?> — Vexogen ERP</title>
<?= \App\Core\CSRF::meta() ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="icon" type="image/png" href="<?= asset('images/vexogen-logo.png') ?>">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>
<body>
<div class="app">
<?php \App\Core\View::partial('partials/sidebar', ['page' => $page ?? 'dashboard']); ?>
<button class="sidebar-backdrop" id="sidebarBackdrop" type="button" aria-label="Close navigation"></button>
<div class="main">
<?php \App\Core\View::partial('partials/topbar', ['title' => $title ?? 'Dashboard', 'page' => $page ?? 'dashboard']); ?>
<?php if ($msg = flash('success')): ?>
<div class="alert alert-success" style="margin:16px 28px 0;padding:12px 16px;background:var(--success-light);color:var(--success);border-radius:8px;font-size:13px"><?= e($msg) ?></div>
<?php endif; ?>
<div class="content">
<?= $content ?>
</div>
</div>
</div>
<?php \App\Core\View::partial('partials/modals'); ?>
<script>window.APP = { baseUrl: '<?= e(url()) ?>', csrf: '<?= e(\App\Core\CSRF::token()) ?>' };</script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
