<?php
$user = \App\Core\Auth::user();
$parts = explode(' ', $user['name'] ?? 'Admin');
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
$unread = \App\Models\Dashboard::unreadCount();
?>
<div class="topbar">
  <div class="topbar-left">
    <button class="icon-btn mobile-menu-btn" id="mobileMenuBtn" type="button" aria-label="Open navigation"><i class="ti ti-menu-2"></i></button>
    <div>
      <div class="page-title"><?= e($title ?? 'Dashboard') ?></div>
      <div class="page-breadcrumb"><span>Vexogen</span><i class="ti ti-chevron-right" style="font-size:11px"></i><span><?= e($title ?? 'Overview') ?></span></div>
    </div>
  </div>
  <div class="search-box" id="globalSearch">
    <i class="ti ti-search"></i>
    <input type="text" id="searchInput" placeholder="Search clients, projects, invoices…" autocomplete="off">
    <div id="searchResults" class="search-dropdown" style="display:none"></div>
  </div>
  <div class="topbar-actions">
    <button class="icon-btn" id="notifBtn" type="button"><i class="ti ti-bell"></i><?php if ($unread): ?><span class="notif-dot"></span><?php endif; ?></button>
    <div class="user-chip">
      <div class="user-avatar"><?= e($initials) ?></div>
      <div class="user-name"><?= e($user['name'] ?? 'User') ?></div>
      <i class="ti ti-chevron-down" style="font-size:13px;color:var(--text-muted)"></i>
    </div>
  </div>
</div>
<div class="notif-panel" id="notifPanel">
  <div class="notif-panel-header">
    <span style="font-size:14px;font-weight:600">Notifications</span>
    <span id="markAllRead" style="font-size:11.5px;color:var(--primary);cursor:pointer">Mark all read</span>
  </div>
  <div id="notifList"></div>
</div>
