<div class="report-shell">
  <div class="section-header finance-page-header report-hero">
    <div>
      <div class="page-kicker">Business intelligence</div>
      <div class="section-title">Business & Finance Reports</div>
      <div class="chart-sub">Actual receipts, expenses, receivables, clients, and service performance for <?= e($rangeLabel) ?>.</div>
    </div>
    <form method="get" class="report-filter-bar">
      <div class="report-filter-field">
        <label class="form-label" for="reportYear">Financial year</label>
        <select id="reportYear" name="year" class="form-control" onchange="this.form.submit()">
          <?php for ($reportYear = (int)date('Y'); $reportYear >= (int)date('Y') - 6; $reportYear--):
            $label = ($fysMonth === 1) ? (string)$reportYear : $reportYear . '-' . substr((string)($reportYear + 1), 2);
          ?>
            <option value="<?= $reportYear ?>" <?= $year === $reportYear ? 'selected' : '' ?>><?= $label ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="report-filter-field">
        <label class="form-label" for="reportFromDate">From</label>
        <input id="reportFromDate" type="date" name="from_date" value="<?= e($startDate) ?>" class="form-control">
      </div>
      <div class="report-filter-field">
        <label class="form-label" for="reportToDate">To</label>
        <input id="reportToDate" type="date" name="to_date" value="<?= e($endDate) ?>" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i>Apply</button>
      <a href="<?= url('reports?year=' . $year) ?>" class="btn btn-outline"><i class="ti ti-rotate-clockwise"></i>Reset</a>
    </form>
  </div>

  <div class="report-kpi">
    <div class="kpi-card">
      <div class="kpi-icon blue"><i class="ti ti-cash-banknote"></i></div>
      <div class="kpi-label">Revenue Collected</div>
      <div class="kpi-val"><?= format_money($ytdRevenue, true) ?></div>
      <div class="kpi-foot">Actual receipts in selected dates</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon orange"><i class="ti ti-receipt-2"></i></div>
      <div class="kpi-label">Operating Expenses</div>
      <div class="kpi-val"><?= format_money($ytdExpenses, true) ?></div>
      <div class="kpi-foot">Recorded expenses in selected dates</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon green"><i class="ti ti-trending-up"></i></div>
      <div class="kpi-label">Net Cash Profit</div>
      <div class="kpi-val <?= $ytdProfit >= 0 ? 'success-text' : 'danger-text' ?>"><?= format_money($ytdProfit, true) ?></div>
      <div class="kpi-foot"><?= e((string)$profitMargin) ?>% cash margin</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon purple"><i class="ti ti-file-invoice"></i></div>
      <div class="kpi-label">Invoice Collection</div>
      <div class="kpi-val"><?= format_money($invoiceSummary['received'] ?? 0, true) ?></div>
      <div class="kpi-foot">of <?= format_money($invoiceSummary['billed'] ?? 0, true) ?> billed</div>
    </div>
  </div>

  <div class="charts-row report-primary-charts">
    <div class="chart-card">
      <div class="chart-title">Monthly Cash Performance</div>
      <div class="chart-sub">Revenue, expenses, and net profit for <?= e($rangeLabel) ?></div>
      <canvas id="reportChart" height="190"></canvas>
    </div>
    <div class="chart-card">
      <div class="chart-title">Revenue by Service</div>
      <div class="chart-sub">Based on payments linked to projects</div>
      <canvas id="serviceChart" height="210"></canvas>
    </div>
  </div>

  <div class="report-section-grid">
    <section class="table-card">
      <div class="table-card-head"><div><strong>Receivables Aging</strong><span>Current outstanding invoice balances</span></div><i class="ti ti-hourglass-high"></i></div>
      <div class="aging-grid">
        <div><span>Current</span><strong><?= format_money($receivables['current_due'] ?? 0) ?></strong></div>
        <div><span>1-30 Days</span><strong><?= format_money($receivables['overdue_30'] ?? 0) ?></strong></div>
        <div><span>31-60 Days</span><strong class="warning-text"><?= format_money($receivables['overdue_60'] ?? 0) ?></strong></div>
        <div><span>60+ Days</span><strong class="danger-text"><?= format_money($receivables['overdue_60_plus'] ?? 0) ?></strong></div>
      </div>
    </section>
    <section class="table-card">
      <div class="table-card-head"><div><strong>Invoice Health</strong><span><?= (int)($invoiceSummary['invoices'] ?? 0) ?> invoices issued in selected dates</span></div><i class="ti ti-activity-heartbeat"></i></div>
      <div class="aging-grid">
        <div><span>Billed</span><strong><?= format_money($invoiceSummary['billed'] ?? 0) ?></strong></div>
        <div><span>Received</span><strong class="success-text"><?= format_money($invoiceSummary['received'] ?? 0) ?></strong></div>
        <div><span>Pending</span><strong class="warning-text"><?= format_money($invoiceSummary['pending'] ?? 0) ?></strong></div>
        <div><span>Completed Projects</span><strong><?= (int)$completedProjects ?></strong></div>
      </div>
    </section>
  </div>

  <div class="report-data-grid">
    <section class="table-card">
      <div class="table-card-head"><div><strong>Top Clients</strong><span>Ranked by actual payments received</span></div><i class="ti ti-users"></i></div>
      <div class="table-wrap"><table>
        <thead><tr><th>Client</th><th>Payments</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($topClients as $client): ?><tr><td><strong><?= e($client['company_name']) ?></strong></td><td><?= (int)$client['payments'] ?></td><td><strong><?= format_money($client['revenue']) ?></strong></td></tr><?php endforeach; ?>
        <?php if (!$topClients): ?><tr><td colspan="3" class="empty-cell">No client revenue for <?= e($rangeLabel) ?>.</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </section>
    <section class="table-card">
      <div class="table-card-head"><div><strong>Payment Methods</strong><span>Transaction channel breakdown</span></div><i class="ti ti-credit-card"></i></div>
      <div class="table-wrap"><table>
        <thead><tr><th>Method</th><th>Transactions</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($paymentMethods as $method): ?><tr><td><span class="badge badge-blue"><?= e(strtoupper($method['payment_method'])) ?></span></td><td><?= (int)$method['transactions'] ?></td><td><strong><?= format_money($method['total']) ?></strong></td></tr><?php endforeach; ?>
        <?php if (!$paymentMethods): ?><tr><td colspan="3" class="empty-cell">No payments for <?= e($rangeLabel) ?>.</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </section>
    <section class="table-card">
      <div class="table-card-head"><div><strong>Expense Categories</strong><span>Where operating cash was spent</span></div><i class="ti ti-category"></i></div>
      <div class="table-wrap"><table>
        <thead><tr><th>Category</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($expenseCategories as $category): ?><tr><td><?= e(status_label($category['category'])) ?></td><td><strong><?= format_money($category['total']) ?></strong></td></tr><?php endforeach; ?>
        <?php if (!$expenseCategories): ?><tr><td colspan="2" class="empty-cell">No expenses for <?= e($rangeLabel) ?>.</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </section>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const chartData = <?= json_encode($chartData) ?>;
  const services = <?= json_encode(array_map(fn($row) => status_label($row['category']), $serviceRevenue)) ?>;
  const serviceTotals = <?= json_encode(array_map(fn($row) => round((float)$row['total'] / 1000, 1), $serviceRevenue)) ?>;
  const moneyTick = value => '\u20B9' + value + 'K';
  if (document.getElementById('reportChart')) {
    new Chart(document.getElementById('reportChart'), {
      type: 'line',
      data: { labels: chartData.months, datasets: [
        { label: 'Revenue', data: chartData.revenue, borderColor: '#0F62FE', backgroundColor: 'rgba(15,98,254,.08)', fill: true, tension: .32 },
        { label: 'Expenses', data: chartData.expenses, borderColor: '#F59E0B', backgroundColor: 'rgba(245,158,11,.04)', fill: false, tension: .32 },
        { label: 'Net Profit', data: chartData.profit, borderColor: '#16A34A', backgroundColor: 'rgba(22,163,74,.04)', fill: false, tension: .32 }
      ]},
      options: { responsive: true, interaction: { mode: 'index', intersect: false }, plugins: { legend: { labels: { usePointStyle: true } } }, scales: { x: { grid: { display: false } }, y: { ticks: { callback: moneyTick }, grid: { color: '#F1F5F9' } } } }
    });
  }
  if (document.getElementById('serviceChart') && services.length) {
    new Chart(document.getElementById('serviceChart'), {
      type: 'bar',
      data: { labels: services, datasets: [{ label: 'Revenue', data: serviceTotals, backgroundColor: '#0F62FE', borderRadius: 5 }] },
      options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { callback: moneyTick }, grid: { color: '#F1F5F9' } }, y: { grid: { display: false } } } }
    });
  }
});
</script>
