<?php $s = $stats; $sc = $statusChart; ?>
<div style="background: var(--card-bg); padding: 16px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="margin: 0; font-size: 18px; font-weight: 600;">Dashboard Filter</h2>
        <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Filter dashboard statistics and lists</div>
    </div>
    <form method="get" action="<?= url('dashboard') ?>" style="display: flex; gap: 12px; align-items: center;">
        <input type="month" name="month" value="<?= e($filters['month'] ?? '') ?>" class="form-control" style="width: 150px;">
        <select name="status" class="form-control" style="width: 160px;">
            <option value="">All Project Statuses</option>
            <option value="lead" <?= ($filters['status'] ?? '') === 'lead' ? 'selected' : '' ?>>Lead</option>
            <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="review" <?= ($filters['status'] ?? '') === 'review' ? 'selected' : '' ?>>Review</option>
            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
        </select>
        <select name="priority" class="form-control" style="width: 150px;">
            <option value="">All Task Priorities</option>
            <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low Priority</option>
            <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium Priority</option>
            <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High Priority</option>
            <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="<?= url('dashboard') ?>" class="btn btn-outline">Clear</a>
    </form>
</div>
<div class="stats-grid">
  <div class="stat-card"><div class="stat-icon blue"><i class="ti ti-building-store"></i></div><div class="stat-label">Total Clients</div><div class="stat-value"><?= (int) $s['total_clients'] ?></div></div>
  <div class="stat-card"><div class="stat-icon blue"><i class="ti ti-briefcase"></i></div><div class="stat-label">Active Projects</div><div class="stat-value"><?= (int) $s['active_projects'] ?></div></div>
  <div class="stat-card"><div class="stat-icon green"><i class="ti ti-circle-check"></i></div><div class="stat-label">Completed</div><div class="stat-value"><?= (int) $s['completed_projects'] ?></div></div>
  <div class="stat-card"><div class="stat-icon orange"><i class="ti ti-coin"></i></div><div class="stat-label">Revenue (Month)</div><div class="stat-value"><?= format_money($s['revenue_month'], true) ?></div></div>
  <div class="stat-card"><div class="stat-icon red"><i class="ti ti-alert-triangle"></i></div><div class="stat-label">Pending Payments</div><div class="stat-value"><?= format_money($s['pending_payments'], true) ?></div></div>
</div>
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-label">Billed This Month</div><div class="stat-value" style="font-size:20px"><?= format_money($s['billed_month'], true) ?></div><div class="stat-change">Invoices issued</div></div>
  <div class="stat-card"><div class="stat-label">Expenses This Month</div><div class="stat-value" style="font-size:20px"><?= format_money($s['expenses_month'], true) ?></div><div class="stat-change">Recorded operating costs</div></div>
  <div class="stat-card"><div class="stat-label">Net Cash Profit</div><div class="stat-value" style="font-size:20px;color:<?= $s['net_profit'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>"><?= format_money($s['net_profit'], true) ?></div><div class="stat-change"><?= (int)$s['margin'] ?>% margin</div></div>
  <div class="stat-card"><div class="stat-label">Cash Position</div><div class="stat-value" style="font-size:20px;color:<?= $s['cash_in_hand'] >= 0 ? 'var(--text-primary)' : 'var(--danger)' ?>"><?= format_money($s['cash_in_hand'], true) ?></div><div class="stat-change">All receipts minus all expenses</div></div>
  <div class="stat-card"><div class="stat-label">Collection Rate</div><div class="stat-value" style="font-size:20px"><?= e((string)$s['collection_rate']) ?>%</div><div class="stat-change <?= $s['overdue_payments'] > 0 ? 'down' : 'up' ?>"><?= format_money($s['overdue_payments'], true) ?> overdue</div></div>
</div>
<div class="charts-row">
  <div class="chart-card">
    <div class="chart-title">Revenue & Expenses</div>
    <div class="chart-sub">Monthly overview — <?= date('Y') ?></div>
    <canvas id="revenueChart" height="180"></canvas>
  </div>
  <div class="chart-card">
    <div class="chart-title">Project Status</div>
    <div class="chart-sub">Distribution across all projects</div>
    <div style="display:flex;align-items:center;gap:20px;margin-top:8px">
      <div style="position:relative;width:120px;height:120px;flex-shrink:0"><canvas id="statusChart" width="120" height="120"></canvas></div>
      <div class="donut-legend">
        <div class="legend-item"><div class="legend-dot" style="background:#0F62FE"></div><div class="legend-label">Active</div><div class="legend-val"><?= (int) $sc['active'] ?></div></div>
        <div class="legend-item"><div class="legend-dot" style="background:#16A34A"></div><div class="legend-label">Completed</div><div class="legend-val"><?= (int) $sc['completed'] ?></div></div>
        <div class="legend-item"><div class="legend-dot" style="background:#F59E0B"></div><div class="legend-label">Review</div><div class="legend-val"><?= (int) $sc['review'] ?></div></div>
        <div class="legend-item"><div class="legend-dot" style="background:#E2E8F0"></div><div class="legend-label">Lead</div><div class="legend-val"><?= (int) $sc['lead'] ?></div></div>
      </div>
    </div>
  </div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px">
  <div class="table-card">
    <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
      <div style="font-size:14px;font-weight:600">Recent Projects</div>
      <a href="<?= url('projects') ?>" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <div class="table-wrap"><table>
      <thead><tr><th>Project</th><th>Client</th><th>Status</th><th>Progress</th></tr></thead>
      <tbody>
      <?php foreach ($recentProjects as $p): ?>
        <tr>
          <td><div style="font-weight:500"><?= e($p['name']) ?></div><div style="font-size:11px;color:var(--text-muted)"><?= e($p['project_code']) ?></div></td>
          <td><?= e($p['client_name']) ?></td>
          <td><span class="badge <?= status_badge_class($p['status']) ?>"><?= e(status_label($p['status'])) ?></span></td>
          <td><div class="progress" style="width:80px"><div class="progress-fill" style="width:<?= (int) $p['completion_percentage'] ?>%"></div></div></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
  <div class="table-card">
    <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
      <div style="font-size:14px;font-weight:600">Pending Tasks</div>
      <a href="<?= url('tasks') ?>" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <div class="table-wrap"><table>
      <thead><tr><th>Task</th><th>Assignee</th><th>Priority</th><th>Due</th></tr></thead>
      <tbody>
      <?php foreach ($pendingTasks as $t): ?>
        <tr>
          <td style="font-weight:500"><?= e($t['title']) ?></td>
          <td><?= e($t['assignee_name'] ?? '—') ?></td>
          <td><span class="badge <?= status_badge_class($t['priority']) ?>"><?= e(ucfirst($t['priority'])) ?></span></td>
          <td><?= format_date($t['due_date']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</div>
<div class="table-card">
  <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <div style="font-size:14px;font-weight:600">Recent Payments</div>
    <button class="btn btn-outline btn-sm" type="button" data-open="paymentModal"><i class="ti ti-plus"></i> Record Payment</button>
  </div>
  <div class="table-wrap"><table>
    <thead><tr><th>Invoice</th><th>Client</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($recentPayments as $py): ?>
      <tr>
        <td style="color:var(--primary);font-weight:500"><?= e($py['invoice_number'] ?? '—') ?></td>
        <td><?= e($py['client_name']) ?></td>
        <td style="font-weight:600"><?= format_money($py['amount']) ?></td>
        <td><span class="badge badge-gray"><?= e(strtoupper($py['payment_method'])) ?></span></td>
        <td><?= format_date($py['payment_date']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const chartData = <?= json_encode($chartData) ?>;
  const statusData = <?= json_encode(array_values($sc)) ?>;
  if (document.getElementById('revenueChart')) {
    new Chart(document.getElementById('revenueChart'), {
      type: 'bar',
      data: {
        labels: chartData.months,
        datasets: [
          { label: 'Revenue', data: chartData.revenue, backgroundColor: '#0F62FE', borderRadius: 4 },
          { label: 'Expenses', data: chartData.expenses, backgroundColor: '#E2E8F0', borderRadius: 4 }
        ]
      },
      options: { responsive: true, plugins: { legend: { position: 'top', labels: { font: { family: 'Inter', size: 11 }, usePointStyle: true } } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#F1F5F9' }, ticks: { callback: v => '₹' + v + 'K' } } } }
    });
  }
  if (document.getElementById('statusChart')) {
    new Chart(document.getElementById('statusChart'), {
      type: 'doughnut',
      data: { labels: ['Active','Completed','Review','Lead'], datasets: [{ data: statusData, backgroundColor: ['#0F62FE','#16A34A','#F59E0B','#E2E8F0'], borderWidth: 0 }] },
      options: { responsive: false, cutout: '72%', plugins: { legend: { display: false } } }
    });
  }
});
</script>
