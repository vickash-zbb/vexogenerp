<div class="tabs" style="margin-bottom:24px" id="settingsTabs">
  <div class="tab active" data-panel="companyPanel">Company</div>
  <div class="tab" data-panel="invoicePanel">Invoice & Email</div>
  <div class="tab" data-panel="notifyPanel">Notifications</div>
  <div class="tab" data-panel="backupPanel">Backup</div>
  <div class="tab" data-panel="usersPanel">Users & Roles</div>
</div>

<div id="companyPanel" class="chart-card" >
  <div style="font-size:15px;font-weight:600;margin-bottom:20px">Company Information</div>
  <form class="ajax-form settings-form" data-endpoint="/api/settings" data-method="POST" enctype="multipart/form-data">
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Company Name</label><input class="form-control" name="company_name" value="<?= e($settings['company_name'] ?? 'Vexogen') ?>"></div>
      <div class="form-group"><label class="form-label">GST Number</label><input class="form-control" name="gst_number" value="<?= e($settings['gst_number'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($settings['phone'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= e($settings['email'] ?? '') ?>"></div>
      <div class="form-group" style="grid-column:1/-1"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"><?= e($settings['address'] ?? '') ?></textarea></div>
      <div class="form-group"><label class="form-label">Website</label><input class="form-control" name="website" value="<?= e($settings['website'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">UPI ID</label><input class="form-control" name="upi_id" value="<?= e($settings['upi_id'] ?? '') ?>" placeholder="vexogen@upi"></div>
      <div class="form-group">
          <label class="form-label">Logo</label>
          <div style="display:flex;gap:10px;">
              <input class="form-control" name="logo_path" value="<?= e($settings['logo_path'] ?? 'assets/images/vexogen-logo.png') ?>" placeholder="Path or upload ->">
              <input type="file" class="form-control" name="logo_file" accept="image/*">
          </div>
      </div>
      <div class="form-group">
          <label class="form-label">Signature</label>
          <div style="display:flex;gap:10px;">
              <input class="form-control" name="signature_path" value="<?= e($settings['signature_path'] ?? '') ?>" placeholder="Path or upload ->">
              <input type="file" class="form-control" name="signature_file" accept="image/*">
          </div>
      </div>
      <div class="form-group"><label class="form-label">Bank Name</label><input class="form-control" name="bank_name" value="<?= e($settings['bank_name'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Bank Account</label><input class="form-control" name="bank_account" value="<?= e($settings['bank_account'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">IFSC</label><input class="form-control" name="bank_ifsc" value="<?= e($settings['bank_ifsc'] ?? '') ?>"></div>
    </div>
    <div style="margin-top:20px;text-align:right"><button type="submit" class="btn btn-primary">Save Changes</button></div>
  </form>
</div>

<div id="invoicePanel" style="display:none" class="chart-card" style="max-width:720px">
  <div style="font-size:15px;font-weight:600;margin-bottom:20px">Invoice & SMTP Settings</div>
  <form class="ajax-form settings-form" data-endpoint="/api/settings" data-method="POST">
    <div class="form-grid">
      <div class="form-group" style="grid-column:1/-1"><label class="form-label">Invoice Terms</label><textarea class="form-control" name="invoice_terms" rows="3"><?= e($settings['invoice_terms'] ?? '') ?></textarea></div>
      <div class="form-group" style="grid-column:1/-1"><label class="form-label">Quotation Terms</label><textarea class="form-control" name="quotation_terms" rows="3"><?= e($settings['quotation_terms'] ?? '') ?></textarea></div>
      <div class="form-group"><label class="form-label">SMTP Host</label><input class="form-control" name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com"></div>
      <div class="form-group"><label class="form-label">SMTP Port</label><input class="form-control" name="smtp_port" type="number" value="<?= e((string)($settings['smtp_port'] ?? 587)) ?>"></div>
      <div class="form-group"><label class="form-label">SMTP Username</label><input class="form-control" name="smtp_user" value="<?= e($settings['smtp_user'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">SMTP Password</label><input class="form-control" name="smtp_pass" type="password" placeholder="Leave blank to keep current"></div>
      <div class="form-group"><label class="form-label">Encryption</label><select class="form-control" name="smtp_encryption"><option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option><option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option></select></div>
    </div>
    <p style="font-size:12px;color:var(--text-muted);margin-top:12px">Configure SMTP to send invoices and quotations by email. Without SMTP, PHP mail() is used as fallback.</p>
    <div style="margin-top:20px;text-align:right"><button type="submit" class="btn btn-primary">Save Settings</button></div>
  </form>
</div>

<div id="notifyPanel" style="display:none" class="chart-card" style="max-width:720px">
  <div style="font-size:15px;font-weight:600;margin-bottom:20px">Notification Preferences</div>
  <form class="ajax-form settings-form" data-endpoint="/api/settings" data-method="POST">
    <div style="display:flex;flex-direction:column;gap:14px">
      <label style="display:flex;align-items:center;gap:10px;font-size:14px"><input type="hidden" name="notify_payment_overdue" value="0"><input type="checkbox" name="notify_payment_overdue" value="1" <?= ($settings['notify_payment_overdue'] ?? 1) ? 'checked' : '' ?>> Payment overdue alerts</label>
      <label style="display:flex;align-items:center;gap:10px;font-size:14px"><input type="hidden" name="notify_deadline" value="0"><input type="checkbox" name="notify_deadline" value="1" <?= ($settings['notify_deadline'] ?? 1) ? 'checked' : '' ?>> Deadline reminders</label>
      <label style="display:flex;align-items:center;gap:10px;font-size:14px"><input type="hidden" name="notify_task_assigned" value="0"><input type="checkbox" name="notify_task_assigned" value="1" <?= ($settings['notify_task_assigned'] ?? 1) ? 'checked' : '' ?>> Task assigned notifications</label>
    </div>
    <div style="margin-top:20px;text-align:right"><button type="submit" class="btn btn-primary">Save Preferences</button></div>
  </form>
</div>

<div id="backupPanel" style="display:none" class="chart-card" style="max-width:720px">
  <div style="font-size:15px;font-weight:600;margin-bottom:12px">Database Backup</div>
  <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">Create manual backups or schedule automatic backups via Windows Task Scheduler / cron.</p>
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <button type="button" class="btn btn-primary" id="createBackupBtn"><i class="ti ti-database"></i> Create Backup Now</button>
    <button type="button" class="btn btn-outline" id="generateTokenBtn"><i class="ti ti-key"></i> Generate Cron Token</button>
  </div>
  <div id="cronUrlBox" style="display:<?= !empty($settings['backup_token']) ? 'block' : 'none' ?>;background:var(--bg);padding:12px;border-radius:8px;margin-bottom:16px;font-size:12px">
    <div style="font-weight:600;margin-bottom:6px">Cron URL (daily at 2 AM example):</div>
    <code id="cronUrlText"><?= e($cronUrl) ?>?token=<?= e($settings['backup_token'] ?? '') ?></code>
  </div>
  <div class="table-card"><div class="table-wrap"><table>
    <thead><tr><th>Filename</th><th>Size</th><th>Created</th></tr></thead>
    <tbody>
    <?php foreach ($backups as $b): ?>
      <tr><td><?= e($b['filename']) ?></td><td><?= number_format($b['size']/1024, 1) ?> KB</td><td><?= e($b['created_at']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (empty($backups)): ?><tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px">No backups yet</td></tr><?php endif; ?>
    </tbody>
  </table></div></div>
</div>

<div id="usersPanel" style="display:none" class="table-card" style="max-width:800px">
  <div class="table-wrap"><table>
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td style="font-weight:500"><?= e($u['name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><span class="badge badge-blue"><?= e(ucfirst($u['role'])) ?></span></td>
        <td><span class="badge <?= $u['is_active'] ? 'badge-green' : 'badge-gray' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
        <td><?= $u['last_login_at'] ? format_date($u['last_login_at'], 'M j, Y g:i A') : '—' ?></td>
        <td><div class="table-actions">
          <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="user" data-id="<?= (int)$u['id'] ?>" data-record="<?= e(json_encode($u)) ?>" title="Edit"><i class="ti ti-edit"></i></button>
          <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="user" data-id="<?= (int)$u['id'] ?>" data-label="<?= e($u['name']) ?>" title="Delete"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
        </div></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
