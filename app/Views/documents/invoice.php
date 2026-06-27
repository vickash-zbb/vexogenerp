<?php
/** @var array $invoice */
/** @var array $settings */
$inv = $invoice;
$set = $settings;

$money = static fn($value): string => '&#8377;' . number_format((float) $value, 2, '.', ',');
$invoiceDate = !empty($inv['invoice_date']) ? strtotime($inv['invoice_date']) : time();
$dueDate = !empty($inv['due_date']) ? strtotime($inv['due_date']) : null;
$paymentDays = $dueDate ? max(0, (int) ceil(($dueDate - $invoiceDate) / 86400)) : 0;
$paymentTerms = $paymentDays > 0 ? "Net {$paymentDays} days" : 'Due on receipt';
$received = (float) ($inv['received_amount'] ?? 0);
$balance = (float) ($inv['pending_amount'] ?? 0);
$advance = (float) ($inv['advance_paid'] ?? 0);
$clientAddress = array_filter([
    $inv['client_address'] ?? null,
    implode(', ', array_filter([$inv['client_city'] ?? null, $inv['client_state'] ?? null])),
    $inv['client_pincode'] ?? null,
]);
$companyAddress = array_filter([
    $set['address'] ?? null,
    implode(', ', array_filter([$set['city'] ?? null, $set['state'] ?? null])),
    $set['pincode'] ?? null,
]);

$assetDataUri = static function (?string $path): ?string {
    if (!$path) {
        return null;
    }
    if (str_starts_with($path, 'data:')) {
        return $path;
    }
    $candidates = [
        PUBLIC_PATH . '/' . ltrim($path, '/'),
        STORAGE_PATH . '/uploads/' . ltrim($path, '/'),
        BASE_PATH . '/' . ltrim($path, '/'),
    ];
    foreach ($candidates as $candidate) {
        if (is_file($candidate) && is_readable($candidate)) {
            $mime = mime_content_type($candidate) ?: 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($candidate));
        }
    }
    return null;
};

$logo = $assetDataUri($set['logo_path'] ?? null);
$logo = $logo ?: $assetDataUri('assets/images/vexogen-logo.png');
$signature = $assetDataUri($set['signature_path'] ?? null);
$upiPayload = !empty($set['upi_id'])
    ? 'upi://pay?pa=' . rawurlencode($set['upi_id']) .
      '&pn=' . rawurlencode($set['company_name'] ?? 'Vexogen') .
      '&am=' . number_format($balance, 2, '.', '') .
      '&cu=INR&tn=' . rawurlencode('Invoice ' . $inv['invoice_number'])
    : '';
$qrUrl = $upiPayload !== ''
    ? 'https://quickchart.io/qr?size=160&margin=1&text=' . rawurlencode($upiPayload)
    : '';
$terms = trim((string) ($set['invoice_terms'] ?? ''));
$termLines = $terms !== '' ? preg_split('/\r\n|\r|\n|(?<=[.!?])\s+/', $terms) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 20mm 16mm 17mm; }
* { box-sizing: border-box; }
body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 9.5px; line-height: 1.45; background: #fff; }
table { border-collapse: collapse; width: 100%; }
.muted { color: #6B7280; }
.accent { color: #2563EB; }
.danger { color: #DC2626; }
.eyebrow { color: #6B7280; font-size: 7.5px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
.header-table td { vertical-align: top; }
.brand-cell { width: 58%; }
.invoice-cell { width: 42%; text-align: right; }
.logo { max-width: 118px; max-height: 44px; margin-bottom: 9px; }
.logo-mark { display: inline-block; width: 34px; height: 34px; line-height: 34px; text-align: center; color: #fff; background: #111827; font-size: 17px; font-weight: 700; margin-bottom: 9px; }
.company-name { font-size: 17px; font-weight: 700; letter-spacing: -.3px; }
.company-lines { color: #6B7280; font-size: 8.4px; margin-top: 5px; line-height: 1.55; }
.invoice-title { font-size: 25px; font-weight: 700; letter-spacing: -.8px; margin-bottom: 12px; }
.meta-table { margin-left: auto; width: 86%; }
.meta-table td { border-bottom: 1px solid #E5E7EB; padding: 4px 0; }
.meta-table td:first-child { color: #6B7280; text-align: left; }
.meta-table td:last-child { font-weight: 700; text-align: right; }
.top-rule { border-top: 2px solid #111827; margin: 18px 0 15px; }
.info-table { table-layout: fixed; margin-bottom: 14px; }
.info-table td { width: 50%; vertical-align: top; border: 1px solid #E5E7EB; padding: 12px 13px; }
.info-table td:first-child { border-right: 0; }
.info-title { font-size: 12px; font-weight: 700; margin: 5px 0 3px; }
.info-line { color: #4B5563; margin-top: 2px; }
.project-strip { background: #F9FAFB; border: 1px solid #E5E7EB; padding: 10px 12px; margin-bottom: 15px; }
.project-table { table-layout: fixed; }
.project-table td { vertical-align: top; padding-right: 10px; }
.project-value { display: block; font-size: 9px; font-weight: 700; margin-top: 3px; }
.service-table { table-layout: fixed; margin-bottom: 12px; }
.service-table th { color: #6B7280; font-size: 7.5px; letter-spacing: .65px; text-transform: uppercase; text-align: left; border-top: 1px solid #111827; border-bottom: 1px solid #D1D5DB; padding: 8px 5px; }
.service-table td { border-bottom: 1px solid #E5E7EB; padding: 9px 5px; vertical-align: top; }
.service-table .num { width: 5%; }
.service-table .desc { width: 39%; }
.service-table .qty { width: 8%; text-align: center; }
.service-table .unit { width: 9%; text-align: center; }
.service-table .rate { width: 14%; text-align: right; }
.service-table .tax { width: 9%; text-align: center; }
.service-table .amount { width: 16%; text-align: right; font-weight: 700; }
.service-name { font-weight: 700; color: #111827; }
.service-desc { color: #6B7280; font-size: 8.3px; margin-top: 2px; }
.summary-layout td { vertical-align: top; }
.words-cell { width: 54%; padding-right: 22px; }
.totals-cell { width: 46%; }
.amount-words { border-left: 2px solid #2563EB; padding: 8px 10px; background: #F9FAFB; margin-bottom: 13px; }
.amount-words strong { display: block; margin-top: 3px; font-size: 9px; }
.totals-table td { padding: 4px 0; }
.totals-table td:first-child { color: #6B7280; }
.totals-table td:last-child { text-align: right; font-weight: 700; }
.grand td { border-top: 1px solid #D1D5DB; padding-top: 7px; color: #111827 !important; font-size: 11px; }
.balance-box { background: #111827; color: #fff; margin-top: 7px; padding: 9px 10px; }
.balance-box table td { color: #fff; font-size: 12px; font-weight: 700; }
.balance-box table td:last-child { text-align: right; }
.payment-card { border: 1px solid #E5E7EB; margin-top: 16px; padding: 12px; page-break-inside: avoid; }
.payment-layout td { vertical-align: top; }
.bank-cell { width: 68%; }
.qr-cell { width: 32%; text-align: right; }
.bank-grid { margin-top: 8px; width: 100%; }
.bank-grid td { padding: 2px 0; }
.bank-grid td:first-child { width: 34%; color: #6B7280; }
.bank-grid td:last-child { font-weight: 700; }
.qr { width: 82px; height: 82px; }
.qr-caption { color: #6B7280; font-size: 7px; margin-top: 3px; }
.bottom-table { margin-top: 16px; table-layout: fixed; page-break-inside: avoid; }
.bottom-table td { vertical-align: top; width: 50%; }
.bottom-table td:first-child { padding-right: 18px; }
.section-title { font-weight: 700; font-size: 9px; margin: 4px 0 6px; }
.terms-list { margin: 0; padding-left: 13px; color: #6B7280; font-size: 8px; }
.terms-list li { margin-bottom: 3px; }
.signature-cell { text-align: right; }
.signature { max-width: 105px; max-height: 40px; margin: 9px 0 3px; }
.signature-space { height: 38px; }
.signature-line { display: inline-block; width: 135px; border-top: 1px solid #9CA3AF; padding-top: 4px; color: #6B7280; }
.footer { border-top: 1px solid #D1D5DB; margin-top: 17px; padding-top: 8px; color: #6B7280; font-size: 7.5px; }
.footer td:last-child { text-align: right; }
</style>
</head>
<body>
<table class="header-table">
  <tr>
    <td class="brand-cell">
      <?php if ($logo): ?><img class="logo" src="<?= $logo ?>" alt="Company logo"><?php else: ?><div class="logo-mark">V</div><?php endif; ?>
      <div class="company-name"><?= e($set['company_name'] ?? 'Vexogen') ?></div>
      <div class="company-lines">
        <?= e(implode(' · ', array_filter([$set['website'] ?? null, $set['email'] ?? null, $set['phone'] ?? null]))) ?><br>
        <?php if (!empty($set['gst_number'])): ?>GSTIN: <?= e($set['gst_number']) ?><br><?php endif; ?>
        <?= e(implode(', ', $companyAddress)) ?>
      </div>
    </td>
    <td class="invoice-cell">
      <div class="invoice-title">TAX INVOICE</div>
      <table class="meta-table">
        <tr><td>Invoice Number</td><td><?= e($inv['invoice_number']) ?></td></tr>
        <tr><td>Invoice Date</td><td><?= e(format_date($inv['invoice_date'], 'd M Y')) ?></td></tr>
        <tr><td>Due Date</td><td><?= e(format_date($inv['due_date'], 'd M Y')) ?></td></tr>
        <tr><td>Payment Terms</td><td><?= e($paymentTerms) ?></td></tr>
        <tr><td>Project ID</td><td><?= e($inv['project_code'] ?? 'General') ?></td></tr>
      </table>
    </td>
  </tr>
</table>

<div class="top-rule"></div>

<table class="info-table">
  <tr>
    <td>
      <div class="eyebrow">Bill To</div>
      <div class="info-title"><?= e($inv['contact_person'] ?: $inv['company_name']) ?></div>
      <div class="info-line"><?= e($inv['company_name']) ?></div>
      <?php if ($clientAddress): ?><div class="info-line"><?= e(implode(', ', $clientAddress)) ?></div><?php endif; ?>
      <?php if (!empty($inv['phone'])): ?><div class="info-line"><?= e($inv['phone']) ?></div><?php endif; ?>
      <?php if (!empty($inv['email'])): ?><div class="info-line"><?= e($inv['email']) ?></div><?php endif; ?>
      <?php if (!empty($inv['client_gst'])): ?><div class="info-line">GSTIN: <?= e($inv['client_gst']) ?></div><?php endif; ?>
    </td>
    <td>
      <div class="eyebrow">Project Information</div>
      <div class="info-title"><?= e($inv['project_name'] ?? 'Professional Services') ?></div>
      <div class="info-line">Service: <?= e(status_label($inv['service_type'] ?? 'professional_services')) ?></div>
      <div class="info-line">Account Manager: <?= e($inv['account_manager'] ?? 'Vexogen Team') ?></div>
      <div class="info-line">Delivery: <?= e(format_date($inv['expected_delivery'] ?? null, 'd M Y')) ?></div>
      <div class="info-line">Status: <?= e(status_label($inv['project_status'] ?? $inv['status'])) ?></div>
    </td>
  </tr>
</table>

<table class="service-table">
  <thead>
    <tr>
      <th class="num">No</th>
      <th class="desc">Description</th>
      <th class="qty">Qty</th>
      <th class="unit">Unit</th>
      <th class="rate">Rate</th>
      <th class="tax">Tax</th>
      <th class="amount">Amount</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($inv['items'] ?? [] as $index => $item): ?>
    <tr>
      <td class="num"><?= $index + 1 ?></td>
      <td class="desc">
        <div class="service-name"><?= e($item['service_name']) ?></div>
        <?php if (!empty($item['description'])): ?><div class="service-desc"><?= e($item['description']) ?></div><?php endif; ?>
      </td>
      <td class="qty"><?= e((string) $item['quantity']) ?></td>
      <td class="unit">Service</td>
      <td class="rate"><?= $money($item['rate']) ?></td>
      <td class="tax"><?= e((string) $inv['gst_rate']) ?>%</td>
      <td class="amount"><?= $money($item['amount']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<table class="summary-layout">
  <tr>
    <td class="words-cell">
      <div class="amount-words">
        <div class="eyebrow">Amount in Words</div>
        <strong><?= e(amount_in_words($inv['total_amount'])) ?></strong>
      </div>
      <div class="section-title">Notes</div>
      <div class="muted"><?= e($inv['notes'] ?: 'Thank you for choosing Vexogen. We appreciate your business.') ?></div>
    </td>
    <td class="totals-cell">
      <table class="totals-table">
        <tr><td>Subtotal</td><td><?= $money($inv['subtotal']) ?></td></tr>
        <tr><td>Discount</td><td>-<?= $money($inv['discount_amount'] ?? 0) ?></td></tr>
        <tr><td>GST (<?= e((string) $inv['gst_rate']) ?>%)</td><td><?= $money($inv['gst_amount']) ?></td></tr>
        <tr class="grand"><td>Grand Total</td><td><?= $money($inv['total_amount']) ?></td></tr>
        <tr><td>Advance Paid</td><td><?= $money($advance) ?></td></tr>
        <tr><td>Received</td><td><?= $money($received) ?></td></tr>
        <tr><td>Outstanding</td><td class="danger"><?= $money($balance) ?></td></tr>
      </table>
      <div class="balance-box">
        <table><tr><td>Balance Due</td><td><?= $money($balance) ?></td></tr></table>
      </div>
    </td>
  </tr>
</table>

<div class="payment-card">
  <table class="payment-layout">
    <tr>
      <td class="bank-cell">
        <div class="eyebrow">Payment Information</div>
        <table class="bank-grid">
          <tr><td>Bank Name</td><td><?= e($set['bank_name'] ?? 'Not configured') ?></td></tr>
          <tr><td>Account Name</td><td><?= e($set['company_name'] ?? 'Vexogen') ?></td></tr>
          <tr><td>Account Number</td><td><?= e($set['bank_account'] ?? 'Not configured') ?></td></tr>
          <tr><td>IFSC</td><td><?= e($set['bank_ifsc'] ?? 'Not configured') ?></td></tr>
          <tr><td>UPI ID</td><td><?= e($set['upi_id'] ?? 'Not configured') ?></td></tr>
          <tr><td>Payment Terms</td><td><?= e($paymentTerms) ?></td></tr>
        </table>
      </td>
      <td class="qr-cell">
        <?php if ($qrUrl): ?>
          <img class="qr" src="<?= e($qrUrl) ?>" alt="UPI payment QR">
          <div class="qr-caption">Scan to pay balance due</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>
</div>

<table class="bottom-table">
  <tr>
    <td>
      <div class="eyebrow">Terms & Conditions</div>
      <ul class="terms-list">
        <?php if ($termLines): ?>
          <?php foreach ($termLines as $line): if (trim((string) $line) === '') continue; ?>
            <li><?= e(trim((string) $line)) ?></li>
          <?php endforeach; ?>
        <?php else: ?>
          <li>Payment is due within the agreed payment period.</li>
          <li>Late payments may incur additional charges.</li>
          <li>Ownership transfers after full payment is received.</li>
        <?php endif; ?>
      </ul>
    </td>
    <td class="signature-cell">
      <div class="eyebrow">For <?= e($set['company_name'] ?? 'Vexogen') ?></div>
      <?php if ($signature): ?><img class="signature" src="<?= $signature ?>" alt="Authorized signature"><?php else: ?><div class="signature-space"></div><?php endif; ?>
      <div><span class="signature-line">Authorized Signature & Company Seal</span></div>
    </td>
  </tr>
</table>

<table class="footer">
  <tr>
    <td><?= e(implode(' · ', array_filter([$set['website'] ?? null, $set['email'] ?? null, $set['phone'] ?? null]))) ?></td>
    <td>Generated by Vexogen CRM · <?= e(date('d M Y')) ?></td>
  </tr>
</table>
</body>
</html>
