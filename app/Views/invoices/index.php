<div class="section-header" style="margin-bottom:20px">
  <div class="section-title">Invoices</div>
  <button class="btn btn-primary" type="button" data-open="invoiceModal"><i class="ti ti-plus"></i> New Invoice</button>
</div>
<?php if ($preview): $inv = $preview; $set = $settings ?? [];
  $invoiceImageUrl = static function (?string $path, ?string $fallback = null): ?string {
    $path = trim((string) ($path ?: $fallback));
    if ($path === '') {
      return null;
    }
    if (preg_match('#^(https?://|data:)#i', $path)) {
      return $path;
    }

    $path = ltrim($path, '/');
    if (str_starts_with($path, 'public/')) {
      $path = substr($path, 7);
    }
    if (is_file(PUBLIC_PATH . '/' . $path)) {
      return url($path);
    }
    if (str_starts_with($path, 'assets/')) {
      return url($path);
    }
    if (is_file(PUBLIC_PATH . '/assets/' . $path)) {
      return asset($path);
    }

    return url($path);
  };
  $logoUrl = $invoiceImageUrl($set['logo_path'] ?? null, 'assets/images/vexogen-logo.png');
  $signatureUrl = $invoiceImageUrl($set['signature_path'] ?? null);
  $invoiceTs = strtotime($inv['invoice_date']);
  $dueTs = $inv['due_date'] ? strtotime($inv['due_date']) : null;
  $termDays = $dueTs ? max(0, (int)ceil(($dueTs - $invoiceTs) / 86400)) : 0;
  $paymentTerms = $termDays > 0 ? "Net {$termDays} days" : 'Due on receipt';
  $balanceDue = (float)$inv['pending_amount'];
  $upiPayload = !empty($set['upi_id']) ? 'upi://pay?pa=' . rawurlencode($set['upi_id']) . '&pn=' . rawurlencode($set['company_name'] ?? 'Vexogen') . '&am=' . number_format($balanceDue, 2, '.', '') . '&cu=INR&tn=' . rawurlencode('Invoice ' . $inv['invoice_number']) : '';
  $qrUrl = $upiPayload ? 'https://quickchart.io/qr?size=180&margin=1&text=' . rawurlencode($upiPayload) : '';
?>
<div class="corporate-invoice" id="invoicePreview">
  <div class="corp-invoice-header">
    <div class="corp-brand">
      <?php if ($logoUrl): ?><img class="corp-logo-image" src="<?= e($logoUrl) ?>" alt="Company logo"><?php endif; ?>
      <div class="corp-company"><?= e($set['company_name'] ?? 'Vexogen') ?></div>
      <div class="corp-muted"><?= e(implode(' · ', array_filter([$set['website'] ?? null, $set['email'] ?? null, $set['phone'] ?? null]))) ?></div>
      <div class="corp-muted"><?= e($set['address'] ?? '') ?><?php if (!empty($set['gst_number'])): ?><br>GSTIN: <?= e($set['gst_number']) ?><?php endif; ?></div>
    </div>
    <div class="corp-document-meta">
      <h2>TAX INVOICE</h2>
      <dl>
        <div><dt>Invoice Number</dt><dd><?= e($inv['invoice_number']) ?></dd></div>
        <div><dt>Invoice Date</dt><dd><?= format_date($inv['invoice_date'], 'd M Y') ?></dd></div>
        <div><dt>Due Date</dt><dd><?= format_date($inv['due_date'], 'd M Y') ?></dd></div>
        <div><dt>Payment Terms</dt><dd><?= e($paymentTerms) ?></dd></div>
        <div><dt>Project ID</dt><dd><?= e($inv['project_code'] ?? 'General') ?></dd></div>
      </dl>
    </div>
  </div>

  <div class="corp-divider"></div>

  <div class="corp-info-grid">
    <section>
      <div class="corp-label">Bill To</div>
      <h3><?= e($inv['contact_person'] ?: $inv['company_name']) ?></h3>
      <p><?= e($inv['company_name']) ?><br><?= e($inv['client_address'] ?? '') ?><br><?= e($inv['phone'] ?? '') ?><br><?= e($inv['email'] ?? '') ?><?php if (!empty($inv['client_gst'])): ?><br>GSTIN: <?= e($inv['client_gst']) ?><?php endif; ?></p>
    </section>
    <section>
      <div class="corp-label">Project Information</div>
      <h3><?= e($inv['project_name'] ?? 'Professional Services') ?></h3>
      <p>Service: <?= e(status_label($inv['service_type'] ?? 'professional_services')) ?><br>Account Manager: <?= e($inv['account_manager'] ?? 'Vexogen Team') ?><br>Delivery: <?= format_date($inv['expected_delivery'] ?? null) ?><br>Status: <?= e(status_label($inv['project_status'] ?? $inv['status'])) ?></p>
    </section>
  </div>

  <div class="table-wrap corp-service-wrap">
    <table class="corp-service-table">
      <thead><tr><th>No</th><th>Description</th><th>Qty</th><th>Unit</th><th>Rate</th><th>Tax</th><th>Amount</th></tr></thead>
      <tbody>
      <?php foreach ($inv['items'] ?? [] as $i => $item): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><strong><?= e($item['service_name']) ?></strong><?php if (!empty($item['description'])): ?><small><?= e($item['description']) ?></small><?php endif; ?></td>
          <td><?= e($item['quantity']) ?></td><td>Service</td>
          <td><?= format_money($item['rate']) ?></td><td><?= e((string)$inv['gst_rate']) ?>%</td>
          <td><strong><?= format_money($item['amount']) ?></strong></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="corp-summary-grid">
    <div>
      <div class="corp-words"><span>Amount in Words</span><strong><?= e(amount_in_words($inv['total_amount'])) ?></strong></div>
      <div class="corp-note"><div class="corp-label">Notes</div><p><?= e($inv['notes'] ?: 'Thank you for choosing Vexogen. We appreciate your business.') ?></p></div>
    </div>
    <div class="corp-totals">
      <div><span>Subtotal</span><strong><?= format_money($inv['subtotal']) ?></strong></div>
      <div><span>Discount</span><strong>-<?= format_money($inv['discount_amount'] ?? 0) ?></strong></div>
      <div><span>GST (<?= e((string)$inv['gst_rate']) ?>%)</span><strong><?= format_money($inv['gst_amount']) ?></strong></div>
      <div class="corp-grand"><span>Grand Total</span><strong><?= format_money($inv['total_amount']) ?></strong></div>
      <div><span>Advance Paid</span><strong><?= format_money($inv['advance_paid'] ?? 0) ?></strong></div>
      <div><span>Received</span><strong><?= format_money($inv['received_amount']) ?></strong></div>
      <div><span>Outstanding</span><strong class="corp-danger"><?= format_money($balanceDue) ?></strong></div>
      <div class="corp-balance"><span>Balance Due</span><strong><?= format_money($balanceDue) ?></strong></div>
    </div>
  </div>

  <div class="corp-payment-card">
    <div>
      <div class="corp-label">Payment Information</div>
      <dl>
        <div><dt>Bank Name</dt><dd><?= e($set['bank_name'] ?? 'Not configured') ?></dd></div>
        <div><dt>Account Name</dt><dd><?= e($set['company_name'] ?? 'Vexogen') ?></dd></div>
        <div><dt>Account Number</dt><dd><?= e($set['bank_account'] ?? 'Not configured') ?></dd></div>
        <div><dt>IFSC</dt><dd><?= e($set['bank_ifsc'] ?? 'Not configured') ?></dd></div>
        <div><dt>UPI ID</dt><dd><?= e($set['upi_id'] ?? 'Not configured') ?></dd></div>
      </dl>
    </div>
    <?php if ($qrUrl): ?><div class="corp-qr"><img src="<?= e($qrUrl) ?>" alt="UPI QR"><span>Scan to pay balance due</span></div><?php endif; ?>
  </div>

  <div class="corp-footer-grid">
    <div><div class="corp-label">Terms & Conditions</div><p><?= nl2br(e($set['invoice_terms'] ?? 'Payment is due within the agreed period. Late payments may incur additional charges. Ownership transfers after full payment.')) ?></p></div>
    <div class="corp-signature">
      <span>For <?= e($set['company_name'] ?? 'Vexogen') ?></span>
      <?php if ($signatureUrl): ?><img src="<?= e($signatureUrl) ?>" alt="Authorized signature"><?php else: ?><div></div><?php endif; ?>
      <strong>Authorized Signature & Company Seal</strong>
    </div>
  </div>

  <div class="corp-generated"><span><?= e(implode(' · ', array_filter([$set['website'] ?? null, $set['email'] ?? null, $set['phone'] ?? null]))) ?></span><span>Generated by Vexogen CRM</span></div>
  <div style="display:flex;gap:8px;margin-top:16px;justify-content:flex-end;flex-wrap:wrap">
    <a href="<?= url('documents/invoice/' . $inv['id'] . '/pdf') ?>" class="btn btn-outline btn-sm" target="_blank"><i class="ti ti-download"></i> Download PDF</a>
    <button type="button" class="btn btn-outline btn-sm doc-email" data-type="invoice" data-id="<?= $inv['id'] ?>"><i class="ti ti-mail"></i> Email</button>
    <button type="button" class="btn btn-primary btn-sm doc-whatsapp" data-type="invoice" data-id="<?= $inv['id'] ?>"><i class="ti ti-brand-whatsapp"></i> WhatsApp</button>
  </div>
</div>
<?php else: ?>
<p style="color:var(--text-muted);padding:40px;text-align:center">No invoices yet. Create your first invoice.</p>
<?php endif; ?>
<div class="table-card"><div class="table-wrap"><table>
  <thead><tr><th>Invoice #</th><th>Client</th><th>Date</th><th>Total</th><th>Pending</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($invoices as $inv): ?>
    <tr>
      <td><a href="<?= url('invoices?preview=' . $inv['id']) ?>" style="color:var(--primary);font-weight:500"><?= e($inv['invoice_number']) ?></a></td>
      <td><?= e($inv['client_name']) ?></td>
      <td><?= format_date($inv['invoice_date']) ?></td>
      <td style="font-weight:600"><?= format_money($inv['total_amount']) ?></td>
      <td><?= format_money($inv['pending_amount']) ?></td>
      <td><span class="badge <?= status_badge_class($inv['status']) ?> badge-dot"><?= e(status_label($inv['status'])) ?></span></td>
      <td><div class="table-actions">
        <a href="<?= url('documents/invoice/' . $inv['id'] . '/pdf') ?>" class="btn btn-ghost btn-sm" title="PDF"><i class="ti ti-download"></i></a>
        <button type="button" class="btn btn-ghost btn-sm doc-email" data-type="invoice" data-id="<?= $inv['id'] ?>" title="Email"><i class="ti ti-mail"></i></button>
        <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="invoice" data-id="<?= (int)$inv['id'] ?>" data-record="<?= e(json_encode($inv)) ?>" title="Edit"><i class="ti ti-edit"></i></button>
        <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="invoice" data-id="<?= (int)$inv['id'] ?>" data-label="<?= e($inv['invoice_number']) ?>" title="Delete"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
