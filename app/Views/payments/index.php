<?php
$st = $stats;
$filters = $filters ?? [];
?>
<div class="section-header finance-page-header">
  <div>
    <div class="section-title">Payment Management</div>
    <div class="chart-sub">Record, allocate, reconcile, and audit every client payment.</div>
  </div>
  <button class="btn btn-primary" type="button" data-open="paymentModal"><i class="ti ti-plus"></i> Record Payment</button>
</div>

<div class="finance-kpi-grid">
  <div class="stat-card"><div class="stat-icon blue"><i class="ti ti-file-invoice"></i></div><div class="stat-label">Total Billed</div><div class="stat-value"><?= format_money($st['total_billed'], true) ?></div></div>
  <div class="stat-card"><div class="stat-icon green"><i class="ti ti-cash"></i></div><div class="stat-label">Total Received</div><div class="stat-value"><?= format_money($st['total_received'], true) ?></div></div>
  <div class="stat-card"><div class="stat-icon orange"><i class="ti ti-clock-dollar"></i></div><div class="stat-label">Pending</div><div class="stat-value"><?= format_money($st['pending'], true) ?></div></div>
  <div class="stat-card"><div class="stat-icon red"><i class="ti ti-alert-triangle"></i></div><div class="stat-label">Overdue</div><div class="stat-value"><?= format_money($st['overdue'], true) ?></div></div>
  <div class="stat-card"><div class="stat-icon blue"><i class="ti ti-calendar-dollar"></i></div><div class="stat-label">Received This Month</div><div class="stat-value"><?= format_money($st['revenue_month'], true) ?></div><div class="stat-change up"><?= (int)$st['month_count'] ?> transactions</div></div>
  <div class="stat-card"><div class="stat-icon <?= $st['unallocated'] > 0 ? 'orange' : 'green' ?>"><i class="ti ti-link-off"></i></div><div class="stat-label">Unallocated</div><div class="stat-value"><?= format_money($st['unallocated'], true) ?></div><div class="stat-change"><?= e((string)$st['collection_rate']) ?>% collection rate</div></div>
</div>

<form method="get" class="finance-filter-card">
  <div class="finance-filter-grid">
    <input type="search" name="q" class="form-control" placeholder="Search client, invoice, project, transaction..." value="<?= e($filters['search'] ?? '') ?>">
    <select name="client_id" class="form-control">
      <option value="">All clients</option>
      <?php foreach ($clients as $client): ?><option value="<?= (int)$client['id'] ?>" <?= (string)($filters['client_id'] ?? '') === (string)$client['id'] ? 'selected' : '' ?>><?= e($client['company_name']) ?></option><?php endforeach; ?>
    </select>
    <select name="project_id" class="form-control">
      <option value="">All projects</option>
      <?php foreach ($projects as $project): ?><option value="<?= (int)$project['id'] ?>" <?= (string)($filters['project_id'] ?? '') === (string)$project['id'] ? 'selected' : '' ?>><?= e($project['project_code'] . ' - ' . $project['name']) ?></option><?php endforeach; ?>
    </select>
    <select name="method" class="form-control">
      <option value="">All methods</option>
      <?php foreach (['upi','neft','rtgs','cash','cheque','card','other'] as $method): ?><option value="<?= e($method) ?>" <?= ($filters['method'] ?? '') === $method ? 'selected' : '' ?>><?= e(strtoupper($method)) ?></option><?php endforeach; ?>
    </select>
    <select name="stage" class="form-control">
      <option value="">All stages</option>
      <?php foreach (['advance','25','50','75','final','other'] as $stage): ?><option value="<?= e($stage) ?>" <?= ($filters['stage'] ?? '') === $stage ? 'selected' : '' ?>><?= e(status_label($stage)) ?></option><?php endforeach; ?>
    </select>
    <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from'] ?? '') ?>" title="From date">
    <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to'] ?? '') ?>" title="To date">
    <div class="finance-filter-actions">
      <button class="btn btn-primary" type="submit"><i class="ti ti-filter"></i> Apply</button>
      <a class="btn btn-outline" href="<?= url('payments') ?>">Reset</a>
    </div>
  </div>
</form>

<div class="table-card">
  <div class="table-card-head">
    <div><strong>Payment Ledger</strong><span style="color:var(--text-muted);font-size:12px;margin-left:8px">- <?= count($payments) ?> records shown</span></div>
    <a href="<?= url('reports') ?>" class="btn btn-outline btn-sm"><i class="ti ti-chart-bar"></i> View Reports</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Client / Project</th><th>Invoice</th><th>Amount</th><th>Stage</th><th>Method</th><th>Reference</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($payments as $payment): ?>
        <tr>
          <td><?= format_date($payment['payment_date'], 'd M Y') ?></td>
          <td><strong><?= e($payment['client_name']) ?></strong><small class="table-subtext"><?= e($payment['project_name'] ?? 'No project') ?></small></td>
          <td>
            <?php if ($payment['invoice_id']): ?><a href="<?= url('invoices?preview=' . $payment['invoice_id']) ?>" class="table-link"><?= e($payment['invoice_number']) ?></a>
            <?php else: ?><span class="badge badge-orange">Unallocated</span><?php endif; ?>
          </td>
          <td><strong class="success-text"><?= format_money($payment['amount']) ?></strong></td>
          <td><span class="badge badge-blue"><?= e(status_label($payment['payment_stage'])) ?></span></td>
          <td><?= e(strtoupper($payment['payment_method'])) ?></td>
          <td><span><?= e($payment['transaction_id'] ?: '-') ?></span><?php if (!empty($payment['notes'])): ?><small class="table-subtext"><?= e($payment['notes']) ?></small><?php endif; ?></td>
          <td><div class="table-actions">
            <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="payment" data-id="<?= (int)$payment['id'] ?>" data-record="<?= e(json_encode($payment)) ?>" title="Edit"><i class="ti ti-edit"></i></button>
            <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="payment" data-id="<?= (int)$payment['id'] ?>" data-label="this payment" title="Delete"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
          </div></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$payments): ?><tr><td colspan="8" class="empty-cell">No payments match the selected filters.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
