<div class="section-header" style="margin-bottom:20px">
  <form method="get" style="display:flex;gap:12px;align-items:center">
    <select class="form-control" name="industry" style="width:160px;padding:7px 11px" onchange="this.form.submit()">
      <option value="">All Industries</option>
      <?php foreach (['FMCG','E-Commerce','Media','Technology'] as $ind): ?>
      <option value="<?= e($ind) ?>" <?= ($filters['industry'] ?? '') === $ind ? 'selected' : '' ?>><?= e($ind) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="search" name="q" class="form-control" placeholder="Search clients…" value="<?= e($filters['search'] ?? '') ?>" style="width:220px;padding:7px 11px">
  </form>
  <button class="btn btn-primary" type="button" data-open="clientModal"><i class="ti ti-plus"></i> Add Client</button>
</div>
<div class="table-card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Client</th><th>Contact</th><th>Industry</th><th>Projects</th><th>Outstanding</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($clients as $c): ?>
        <tr>
          <td><div style="font-weight:600"><?= e($c['company_name']) ?></div><div style="font-size:11px;color:var(--text-muted)"><?= e($c['website'] ?? '') ?></div></td>
          <td><div><?= e($c['contact_person'] ?? '—') ?></div><div style="font-size:11.5px;color:var(--text-muted)"><?= e($c['phone'] ?? '') ?></div></td>
          <td><span class="badge badge-blue"><?= e($c['industry'] ?? '—') ?></span></td>
          <td><?= (int) ($c['active_projects'] ?? 0) ?> active</td>
          <td style="font-weight:600;color:<?= (float)$c['outstanding_balance'] > 0 ? 'var(--danger)' : 'var(--success)' ?>"><?= format_money($c['outstanding_balance']) ?></td>
          <td><span class="badge <?= status_badge_class($c['status']) ?> badge-dot"><?= e(status_label($c['status'])) ?></span></td>
          <td><div class="table-actions">
            <a href="<?= url('clients/' . $c['id']) ?>" class="btn btn-ghost btn-sm" title="View"><i class="ti ti-eye"></i></a>
            <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="client" data-id="<?= (int)$c['id'] ?>" data-record="<?= e(json_encode($c)) ?>" title="Edit"><i class="ti ti-edit"></i></button>
            <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="client" data-id="<?= (int)$c['id'] ?>" data-label="<?= e($c['company_name']) ?>" title="Delete"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
          </div></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($clients)): ?><tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px">No clients found</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
