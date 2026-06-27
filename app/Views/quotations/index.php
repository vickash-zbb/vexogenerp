<div class="section-header" style="margin-bottom:20px">
  <div></div>
  <button class="btn btn-primary" type="button" data-open="quotationModal"><i class="ti ti-plus"></i> New Quotation</button>
</div>
<?php if (!empty($preview)): $q = $preview; $set = $settings ?? []; ?>
<div class="invoice-preview" style="margin-bottom:24px">
  <div class="invoice-header">
    <div><div class="invoice-company"><?= e($set['company_name'] ?? 'Vexogen') ?></div></div>
    <div style="text-align:right"><div class="invoice-tag">QUOTATION</div><div>#<?= e($q['quote_number']) ?></div></div>
  </div>
  <p style="margin:12px 0"><strong>Client:</strong> <?= e($q['company_name']) ?> · <strong>Total:</strong> <?= format_money($q['total_amount']) ?></p>
  <div style="display:flex;gap:8px;justify-content:flex-end">
    <a href="<?= url('documents/quotation/' . $q['id'] . '/pdf') ?>" class="btn btn-outline btn-sm" target="_blank"><i class="ti ti-download"></i> Download PDF</a>
    <button type="button" class="btn btn-outline btn-sm doc-email" data-type="quotation" data-id="<?= $q['id'] ?>"><i class="ti ti-mail"></i> Email</button>
    <button type="button" class="btn btn-primary btn-sm doc-whatsapp" data-type="quotation" data-id="<?= $q['id'] ?>"><i class="ti ti-brand-whatsapp"></i> WhatsApp</button>
  </div>
</div>
<?php endif; ?>
<div class="table-card"><div class="table-wrap"><table>
  <thead><tr><th>Quote #</th><th>Client</th><th>Services</th><th>Amount</th><th>Valid Until</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($quotations as $q): ?>
    <tr>
      <td><a href="<?= url('quotations?preview=' . $q['id']) ?>" style="color:var(--primary);font-weight:500"><?= e($q['quote_number']) ?></a></td>
      <td><?= e($q['client_name']) ?></td>
      <td><?= e($q['services'] ?? $q['subject'] ?? '—') ?></td>
      <td style="font-weight:600"><?= format_money($q['total_amount']) ?></td>
      <td><?= format_date($q['valid_until']) ?></td>
      <td><span class="badge <?= status_badge_class($q['status']) ?> badge-dot"><?= e(status_label($q['status'])) ?></span></td>
      <td><div class="table-actions" style="flex-wrap:wrap;gap:4px">
        <a href="<?= url('documents/quotation/' . $q['id'] . '/pdf') ?>" class="btn btn-ghost btn-sm"><i class="ti ti-download"></i></a>
        <button type="button" class="btn btn-ghost btn-sm doc-email" data-type="quotation" data-id="<?= $q['id'] ?>"><i class="ti ti-mail"></i></button>
        <button type="button" class="btn btn-ghost btn-sm doc-whatsapp" data-type="quotation" data-id="<?= $q['id'] ?>"><i class="ti ti-brand-whatsapp"></i></button>
        <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="quotation" data-id="<?= (int)$q['id'] ?>" data-record="<?= e(json_encode($q)) ?>" title="Edit"><i class="ti ti-edit"></i></button>
        <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="quotation" data-id="<?= (int)$q['id'] ?>" data-label="<?= e($q['quote_number']) ?>" title="Delete"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
        <?php if ($q['status'] !== 'converted'): ?>
        <button class="btn btn-primary btn-sm convert-quote" data-id="<?= $q['id'] ?>">To Project</button>
        <?php endif; ?>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
