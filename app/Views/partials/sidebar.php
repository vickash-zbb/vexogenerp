<?php
$user = \App\Core\Auth::user();
$initials = strtoupper(substr($user['name'] ?? 'A', 0, 1) . substr(strstr($user['name'] ?? 'Admin', ' ') ?: '', 1, 1));
$taskCount = \App\Models\Task::countPending();
$clientCount = \App\Models\Client::count();
$projectStats = \App\Models\Project::stats();
$nav = [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'ti-layout-dashboard', 'url' => url(), 'badge' => null],
    ['id' => 'clients', 'label' => 'Clients', 'icon' => 'ti-building-store', 'url' => url('clients'), 'badge' => $clientCount],
    ['id' => 'projects', 'label' => 'Projects', 'icon' => 'ti-briefcase', 'url' => url('projects'), 'badge' => $projectStats['active']],
    ['id' => 'tasks', 'label' => 'Tasks', 'icon' => 'ti-checklist', 'url' => url('tasks'), 'badge' => $taskCount],
];
$finance = [
    ['id' => 'payments', 'label' => 'Payments', 'icon' => 'ti-credit-card', 'url' => url('payments')],
    ['id' => 'invoices', 'label' => 'Invoices', 'icon' => 'ti-file-invoice', 'url' => url('invoices')],
    ['id' => 'quotations', 'label' => 'Quotations', 'icon' => 'ti-clipboard-text', 'url' => url('quotations')],
    ['id' => 'expenses', 'label' => 'Expenses', 'icon' => 'ti-receipt', 'url' => url('expenses')],
];
$team = [
    ['id' => 'employees', 'label' => 'Employees', 'icon' => 'ti-users', 'url' => url('employees')],
    ['id' => 'files', 'label' => 'Files', 'icon' => 'ti-folder', 'url' => url('files')],
    ['id' => 'calendar', 'label' => 'Calendar', 'icon' => 'ti-calendar', 'url' => url('calendar')],
    ['id' => 'reports', 'label' => 'Reports', 'icon' => 'ti-chart-bar', 'url' => url('reports')],
];
?>
<nav class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">
      <div class="brand-icon"><img src="<?= asset('images/vexogen-logo.png') ?>" alt="Vexogen"></div>
      <div class="brand-copy">
        <div class="brand-name">Vexogen</div>
        <div class="brand-sub">Agency ERP</div>
      </div>
      <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" type="button" aria-label="Collapse sidebar" title="Collapse sidebar"><i class="ti ti-chevrons-left"></i></button>
    </div>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Main</div>
    <ul class="sidebar-nav">
      <?php foreach ($nav as $item): ?>
      <li><a href="<?= e($item['url']) ?>" title="<?= e($item['label']) ?>" class="<?= ($page ?? '') === $item['id'] ? 'active' : '' ?>">
        <i class="ti <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
        <?php if ($item['badge']): ?><span class="badge"><?= (int) $item['badge'] ?></span><?php endif; ?>
      </a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Finance</div>
    <ul class="sidebar-nav">
      <?php foreach ($finance as $item): ?>
      <li><a href="<?= e($item['url']) ?>" title="<?= e($item['label']) ?>" class="<?= ($page ?? '') === $item['id'] ? 'active' : '' ?>">
        <i class="ti <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
      </a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Team</div>
    <ul class="sidebar-nav">
      <?php foreach ($team as $item): ?>
      <li><a href="<?= e($item['url']) ?>" title="<?= e($item['label']) ?>" class="<?= ($page ?? '') === $item['id'] ? 'active' : '' ?>">
        <i class="ti <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
      </a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="sidebar-footer">
    <ul class="sidebar-nav">
      <li><a href="<?= url('settings') ?>" title="Settings" class="<?= ($page ?? '') === 'settings' ? 'active' : '' ?>"><i class="ti ti-settings"></i><span>Settings</span></a></li>
      <li><a href="<?= url('logout') ?>" title="Logout"><i class="ti ti-logout"></i><span>Logout</span></a></li>
    </ul>
  </div>
</nav>
