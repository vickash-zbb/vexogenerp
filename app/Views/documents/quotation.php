<?php
/** @var array $quote */
/** @var array $settings */
$set = $settings;
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>
body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#0F172A;margin:32px}
.header{display:flex;justify-content:space-between;margin-bottom:28px}
.company{font-size:20px;font-weight:bold;color:#0F62FE}
.tag{font-size:26px;font-weight:bold;text-align:right}
.label{font-size:10px;text-transform:uppercase;color:#94A3B8;font-weight:bold;margin-bottom:4px}
table{width:100%;border-collapse:collapse;margin:20px 0}
th{background:#0F62FE;color:#fff;padding:8px;text-align:left;font-size:11px}
td{padding:8px;border-bottom:1px solid #E2E8F0}
.totals{width:220px;margin-left:auto}
.row{display:flex;justify-content:space-between;padding:4px 0}
.total{border-top:1px solid #E2E8F0;margin-top:6px;padding-top:8px;font-weight:bold;font-size:14px}
</style></head><body>
<div class="header">
  <div>
    <div class="company"><?= htmlspecialchars($set['company_name'] ?? 'Vexogen') ?></div>
    <div><?= htmlspecialchars($set['address'] ?? '') ?><br>GST: <?= htmlspecialchars($set['gst_number'] ?? '') ?></div>
  </div>
  <div style="text-align:right">
    <div class="tag">QUOTATION</div>
    <div>#<?= htmlspecialchars($quote['quote_number']) ?></div>
    <div>Date: <?= format_date($quote['created_at']) ?><br>Valid Until: <?= format_date($quote['valid_until']) ?></div>
  </div>
</div>
<div style="margin-bottom:20px"><div class="label">Prepared For</div><strong><?= htmlspecialchars($quote['company_name']) ?></strong></div>
<?php if ($quote['subject']): ?><p><strong>Subject:</strong> <?= htmlspecialchars($quote['subject']) ?></p><?php endif; ?>
<table>
  <thead><tr><th>#</th><th>Service</th><th>Description</th><th>Qty</th><th>Rate</th><th>Amount</th></tr></thead>
  <tbody>
  <?php foreach ($quote['items'] ?? [] as $i => $item): ?>
  <tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($item['service_name']) ?></td><td><?= htmlspecialchars($item['description'] ?? '') ?></td><td><?= $item['quantity'] ?></td><td><?= format_money($item['rate']) ?></td><td><?= format_money($item['amount']) ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
<div class="totals">
  <div class="row"><span>Subtotal</span><span><?= format_money($quote['subtotal']) ?></span></div>
  <div class="row"><span>GST <?= $quote['gst_rate'] ?>%</span><span><?= format_money($quote['gst_amount']) ?></span></div>
  <?php if ((float)$quote['discount_amount'] > 0): ?><div class="row"><span>Discount</span><span>-<?= format_money($quote['discount_amount']) ?></span></div><?php endif; ?>
  <div class="row total"><span>Total</span><span style="color:#0F62FE"><?= format_money($quote['total_amount']) ?></span></div>
</div>
<?php if (!empty($quote['terms'])): ?><div style="margin-top:24px;font-size:11px"><strong>Terms:</strong> <?= htmlspecialchars($quote['terms']) ?></div><?php endif; ?>
</body></html>
