<div style="margin-bottom:20px">
  <a href="<?= url('clients') ?>" class="btn btn-ghost btn-sm"><i class="ti ti-arrow-left"></i> Back to Clients</a>
</div>
<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:24px">
  <div class="chart-card">
    <h2 style="font-size:18px;font-weight:600;margin-bottom:4px"><?= e($client['company_name']) ?></h2>
    <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px"><?= e($client['industry'] ?? '') ?> · <span class="badge <?= status_badge_class($client['status']) ?>"><?= e(status_label($client['status'])) ?></span></p>
    <div class="form-grid">
      <div><div class="form-label">Contact</div><div><?= e($client['contact_person'] ?? '—') ?></div></div>
      <div><div class="form-label">Phone</div><div><?= e($client['phone'] ?? '—') ?></div></div>
      <div><div class="form-label">Email</div><div><?= e($client['email'] ?? '—') ?></div></div>
      <div><div class="form-label">GST</div><div><?= e($client['gst_number'] ?? '—') ?></div></div>
      <div style="grid-column:1/-1"><div class="form-label">Address</div><div><?= e($client['address'] ?? '—') ?></div></div>
      <div style="grid-column:1/-1"><div class="form-label">Notes</div><div style="color:var(--text-secondary)"><?= e($client['notes'] ?? '—') ?></div></div>
    </div>
    <div style="margin-top:16px;display:flex;gap:8px">
      <button type="button" class="btn btn-outline btn-sm crud-edit" data-entity="client" data-id="<?= (int)$client['id'] ?>" data-record="<?= e(json_encode($client)) ?>"><i class="ti ti-edit"></i> Edit</button>
      <a href="<?= url('files?client_id=' . $client['id']) ?>" class="btn btn-outline btn-sm"><i class="ti ti-folder"></i> Files</a>
      <?php if ($client['phone']): ?><a href="<?= \App\Services\WhatsAppService::link($client['phone'], 'Hello ' . $client['contact_person']) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="ti ti-brand-whatsapp"></i> WhatsApp</a><?php endif; ?>
      <button type="button" class="btn btn-outline btn-sm crud-delete" data-entity="client" data-id="<?= (int)$client['id'] ?>" data-label="<?= e($client['company_name']) ?>"><i class="ti ti-trash"></i> Delete</button>
    </div>
  </div>
  <div class="chart-card">
    <div class="stat-label">Outstanding Balance</div>
    <div style="font-size:28px;font-weight:700;color:<?= (float)$client['outstanding_balance']>0?'var(--danger)':'var(--success)' ?>"><?= format_money($client['outstanding_balance']) ?></div>
    <div style="margin-top:16px;font-size:13px;color:var(--text-muted)">Website: <?= e($client['website'] ?? '—') ?></div>
    <div style="font-size:13px;color:var(--text-muted)">Tags: <?= e($client['tags'] ?? '—') ?></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px">
  <div class="table-card"><div style="padding:14px 16px;border-bottom:1px solid var(--border);font-weight:600">Projects</div><div class="table-wrap"><table>
    <thead><tr><th>Project</th><th>Status</th><th>Value</th></tr></thead>
    <tbody>
    <?php foreach ($projects as $p): ?>
      <tr><td><strong><?= e($p['name']) ?></strong></td><td><span class="badge <?= status_badge_class($p['status']) ?>"><?= e(status_label($p['status'])) ?></span></td><td><?= format_money($p['selling_price']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div></div>
  <div class="table-card"><div style="padding:14px 16px;border-bottom:1px solid var(--border);font-weight:600">Recent Payments</div><div class="table-wrap"><table>
    <thead><tr><th>Date</th><th>Amount</th></tr></thead>
    <tbody>
    <?php foreach ($payments as $py): ?>
      <tr><td><?= format_date($py['payment_date']) ?></td><td style="font-weight:600"><?= format_money($py['amount']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div></div>
</div>

<div class="chart-card">
  <div style="font-size:15px;font-weight:600;margin-bottom:14px">Communication Timeline</div>
  <form id="commForm" class="form-grid" style="margin-bottom:20px">
    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
    <div class="form-group"><select class="form-control" name="type"><option value="note">Note</option><option value="phone">Phone</option><option value="email">Email</option><option value="whatsapp">WhatsApp</option><option value="meeting">Meeting</option></select></div>
    <div class="form-group"><input class="form-control" name="subject" placeholder="Subject (optional)"></div>
    <div class="form-group" style="grid-column:1/-1"><textarea class="form-control" name="message" rows="2" placeholder="Log a conversation or note…" required></textarea></div>
    <div style="grid-column:1/-1;text-align:right"><button type="submit" class="btn btn-primary btn-sm">Add Entry</button></div>
  </form>
  <div class="timeline">
    <?php foreach ($communications as $cm): ?>
    <div class="timeline-item">
      <div class="timeline-dot-wrap"><div class="timeline-dot"></div><div class="timeline-line"></div></div>
      <div class="timeline-content">
        <div style="display:flex;gap:8px;align-items:center"><span class="badge badge-gray"><?= e(ucfirst($cm['type'])) ?></span><span class="timeline-time"><?= format_date($cm['created_at'], 'M j, Y g:i A') ?></span></div>
        <?php if ($cm['subject']): ?><div style="font-weight:500;margin-top:4px"><?= e($cm['subject']) ?></div><?php endif; ?>
        <div class="timeline-text"><?= e($cm['message']) ?></div>
        <?php if ($cm['user_name']): ?><div style="font-size:11px;color:var(--text-muted);margin-top:4px">— <?= e($cm['user_name']) ?></div><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($communications)): ?><p style="color:var(--text-muted);font-size:13px">No communications logged yet.</p><?php endif; ?>
  </div>
</div>
