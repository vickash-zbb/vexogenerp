<div class="section-header" style="margin-bottom:20px">
  <form method="get" style="display:flex;gap:8px">
    <input type="month" name="month" class="form-control" value="<?= e($month) ?>" style="width:160px;padding:7px 11px" onchange="this.form.submit()">
  </form>
  <button class="btn btn-primary" type="button" data-open="expenseModal"><i class="ti ti-plus"></i> Add Expense</button>
</div>
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
  <div class="stat-card"><div class="stat-label">Total Expenses</div><div class="stat-value" style="font-size:22px"><?= format_money($monthTotal, true) ?></div></div>
  <div class="stat-card"><div class="stat-label">Salary & Payroll</div><div class="stat-value" style="font-size:22px"><?= format_money($catTotals['salary'], true) ?></div></div>
  <div class="stat-card"><div class="stat-label">Office & Rent</div><div class="stat-value" style="font-size:22px"><?= format_money($catTotals['office_rent'], true) ?></div></div>
  <div class="stat-card"><div class="stat-label">Software & Tools</div><div class="stat-value" style="font-size:22px"><?= format_money($catTotals['software'], true) ?></div></div>
</div>
<div class="table-card"><div class="table-wrap"><table>
  <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Paid Via</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($expenses as $ex): ?>
    <tr>
      <td><?= format_date($ex['expense_date']) ?></td>
      <td><span class="badge badge-purple"><?= e(status_label($ex['category'])) ?></span></td>
      <td><?= e($ex['description']) ?></td>
      <td style="font-weight:600"><?= format_money($ex['amount']) ?></td>
      <td><?= e(status_label($ex['paid_via'])) ?></td>
      <td><div class="table-actions">
        <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="expense" data-id="<?= (int)$ex['id'] ?>" data-record="<?= e(json_encode($ex)) ?>" title="Edit"><i class="ti ti-edit"></i></button>
        <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="expense" data-id="<?= (int)$ex['id'] ?>" data-label="<?= e($ex['description']) ?>" title="Delete"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
