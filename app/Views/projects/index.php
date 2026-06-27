<?php
$selectedClientId = (int) ($selectedClientId ?? 0);
$selectedClient = $selectedClient ?? null;
$summary = $summary ?? [];
$projects = $projects ?? [];
$payments = $payments ?? [];
$quotations = $quotations ?? [];
$invoices = $invoices ?? [];
$files = $files ?? [];
$notes = $notes ?? [];
$timeline = $timeline ?? [];
$clientFilters = $clientFilters ?? [];
$filters = $filters ?? [];
$selectedProject = $projects[0] ?? null;

$withClient = function (array $params = []) use ($selectedClientId, $clientFilters): string {
    return url('projects?' . http_build_query(array_filter(array_merge([
        'client_id' => $selectedClientId ?: null,
        'client_q' => $clientFilters['search'] ?? null,
        'client_status' => $clientFilters['status'] ?? null,
    ], $params), fn($value) => $value !== null && $value !== '')));
};

$clientLink = function (int $clientId) use ($clientFilters): string {
    return url('projects?' . http_build_query(array_filter([
        'client_id' => $clientId,
        'client_q' => $clientFilters['search'] ?? null,
        'client_status' => $clientFilters['status'] ?? null,
    ], fn($value) => $value !== null && $value !== '')));
};

$activeWorkflow = [
    'lead' => 'Lead',
    'quotation_sent' => 'Quotation Sent',
    'advance_received' => 'Advance Received',
    'development' => 'In Progress',
    'review' => 'Review',
    'revision' => 'Revision',
    'completed' => 'Completed',
    'delivered' => 'Delivered',
];
?>

<div class="client-project-page">
  <aside class="client-rail">
    <div class="client-rail-head">
      <div>
        <div class="page-kicker">Project Page</div>
        <h2>Clients</h2>
      </div>
      <button class="btn btn-primary btn-sm" type="button" data-open="clientModal"><i class="ti ti-plus"></i> Add Client</button>
    </div>

    <form method="get" class="client-search-panel">
      <input type="hidden" name="client_id" value="<?= $selectedClientId ?: '' ?>">
      <input class="form-control" type="search" name="client_q" placeholder="Search client..." value="<?= e($clientFilters['search'] ?? '') ?>">
      <select class="form-control" name="client_status" onchange="this.form.submit()">
        <option value="">All Clients</option>
        <?php foreach (['active', 'follow_up', 'lead', 'inactive'] as $status): ?>
          <option value="<?= e($status) ?>" <?= ($clientFilters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(status_label($status)) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-outline btn-sm" type="submit"><i class="ti ti-search"></i> Search</button>
    </form>

    <div class="rail-label">Recently Viewed</div>
    <div class="recent-client-row">
      <?php foreach (array_slice($clientRows, 0, 4) as $recent): ?>
        <a href="<?= $clientLink((int) $recent['id']) ?>" class="<?= (int)$recent['id'] === $selectedClientId ? 'active' : '' ?>">
          <?= e(substr($recent['contact_person'] ?: $recent['company_name'], 0, 1)) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="rail-label">Client List</div>
    <div class="client-card-list">
      <?php foreach ($clientRows as $client): ?>
        <a class="client-card <?= (int)$client['id'] === $selectedClientId ? 'active' : '' ?>" href="<?= $clientLink((int) $client['id']) ?>">
          <div class="client-card-top">
            <div>
              <strong><?= e($client['contact_person'] ?: $client['company_name']) ?></strong>
              <span><?= e($client['company_name']) ?></span>
            </div>
            <span class="badge <?= status_badge_class($client['status']) ?>"><?= e(status_label($client['status'])) ?></span>
          </div>
          <div class="client-card-meta">
            <span><?= (int) $client['active_projects'] ?> Active Projects</span>
            <span class="<?= (float)$client['pending_balance'] > 0 ? 'danger-text' : '' ?>"><?= e(format_money($client['pending_balance'])) ?> Pending</span>
          </div>
        </a>
      <?php endforeach; ?>
      <?php if (empty($clientRows)): ?>
        <div class="empty-panel">No clients match your search.</div>
      <?php endif; ?>
    </div>
  </aside>

  <main class="client-workspace">
    <?php if (!$selectedClient): ?>
      <div class="empty-workspace">
        <i class="ti ti-users"></i>
        <strong>Select a client</strong>
        <span>Choose a client to see projects, payments, files, notes, and activity in one place.</span>
      </div>
    <?php else: ?>
      <section class="client-hero">
        <div>
          <div class="page-kicker">Selected Client</div>
          <h1><?= e($selectedClient['contact_person'] ?: $selectedClient['company_name']) ?></h1>
          <div class="client-company"><?= e($selectedClient['company_name']) ?></div>
          <div class="client-contact-grid">
            <span><i class="ti ti-phone"></i><?= e($selectedClient['phone'] ?: 'No phone') ?></span>
            <span><i class="ti ti-mail"></i><?= e($selectedClient['email'] ?: 'No email') ?></span>
            <span><i class="ti ti-receipt-tax"></i><?= e($selectedClient['gst_number'] ?: 'No GST') ?></span>
            <span><i class="ti ti-map-pin"></i><?= e($selectedClient['address'] ?: 'No address') ?></span>
          </div>
        </div>
        <div class="client-actions">
          <button class="btn btn-outline btn-sm" type="button" data-open="clientEditModal"><i class="ti ti-edit"></i> Edit Client</button>
          <button class="btn btn-outline btn-sm crud-delete" type="button" data-entity="client" data-id="<?= $selectedClientId ?>" data-label="<?= e($selectedClient['company_name']) ?>"><i class="ti ti-trash"></i> Delete</button>
          <a class="btn btn-outline btn-sm" href="<?= $selectedClient['phone'] ? 'https://wa.me/' . preg_replace('/\D+/', '', $selectedClient['phone']) : '#' ?>" target="_blank"><i class="ti ti-brand-whatsapp"></i> WhatsApp</a>
          <a class="btn btn-outline btn-sm" href="<?= $selectedClient['phone'] ? 'tel:' . e($selectedClient['phone']) : '#' ?>"><i class="ti ti-phone-call"></i> Call</a>
          <a class="btn btn-primary btn-sm" href="<?= $selectedClient['email'] ? 'mailto:' . e($selectedClient['email']) : '#' ?>"><i class="ti ti-mail-forward"></i> Email</a>
        </div>
      </section>

      <section class="business-summary-grid">
        <div class="summary-card"><span>Total Projects</span><strong><?= (int) ($summary['total_projects'] ?? 0) ?></strong></div>
        <div class="summary-card"><span>Completed Projects</span><strong><?= (int) ($summary['completed_projects'] ?? 0) ?></strong></div>
        <div class="summary-card"><span>Active Projects</span><strong><?= (int) ($summary['active_projects'] ?? 0) ?></strong></div>
        <div class="summary-card"><span>Total Business</span><strong><?= e(format_money($summary['total_business'] ?? 0)) ?></strong></div>
        <div class="summary-card"><span>Received Amount</span><strong class="success-text"><?= e(format_money($summary['received_amount'] ?? 0)) ?></strong></div>
        <div class="summary-card"><span>Pending Balance</span><strong class="danger-text"><?= e(format_money($summary['pending_balance'] ?? 0)) ?></strong></div>
      </section>

      <section class="workspace-section">
        <div class="section-toolbar">
          <div>
            <h2>Projects</h2>
            <p>All work for <?= e($selectedClient['company_name']) ?>, filtered and ready for action.</p>
          </div>
          <button class="btn btn-primary" type="button" data-open="projectModal" id="addProjectForClient"><i class="ti ti-plus"></i> Add Project</button>
        </div>

        <form method="get" class="project-filter-bar">
          <input type="hidden" name="client_id" value="<?= $selectedClientId ?>">
          <input type="hidden" name="client_q" value="<?= e($clientFilters['search'] ?? '') ?>">
          <input type="hidden" name="client_status" value="<?= e($clientFilters['status'] ?? '') ?>">
          <input class="form-control" type="search" name="project_q" placeholder="Search projects..." value="<?= e($filters['search'] ?? '') ?>">
          <select class="form-control" name="status">
            <option value="">Status</option>
            <?php foreach ($activeWorkflow as $status => $label): ?>
              <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-control" name="service">
            <option value="">Service</option>
            <?php foreach (config('app.service_categories') as $service): ?>
              <option value="<?= e($service) ?>" <?= ($filters['category'] ?? '') === $service ? 'selected' : '' ?>><?= e(status_label($service)) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-control" name="employee_id">
            <option value="">Employee</option>
            <?php foreach ($employees as $employee): ?>
              <option value="<?= (int) $employee['id'] ?>" <?= (string)($filters['assigned_to'] ?? '') === (string)$employee['id'] ? 'selected' : '' ?>><?= e($employee['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-control" name="priority">
            <option value="">Priority</option>
            <?php foreach (['low', 'medium', 'high', 'urgent'] as $priority): ?>
              <option value="<?= e($priority) ?>" <?= ($filters['priority'] ?? '') === $priority ? 'selected' : '' ?>><?= e(ucfirst($priority)) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-control" name="balance">
            <option value="">Balance</option>
            <option value="pending" <?= ($filters['balance'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="paid" <?= ($filters['balance'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
          </select>
          <select class="form-control" name="delivery">
            <option value="">Delivery</option>
            <option value="overdue" <?= ($filters['delivery'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
            <option value="week" <?= ($filters['delivery'] ?? '') === 'week' ? 'selected' : '' ?>>Next 7 Days</option>
          </select>
          <button class="btn btn-outline" type="submit"><i class="ti ti-filter"></i> Filter</button>
        </form>

        <div class="project-card-grid">
          <?php foreach ($projects as $project): ?>
            <?php
              $balance = (float) ($project['balance_amount'] ?? 0);
              $isOverdue = !empty($project['expected_delivery']) && $project['expected_delivery'] < date('Y-m-d') && !in_array($project['status'], ['completed', 'delivered', 'closed'], true);
            ?>
            <article class="client-project-card">
              <div class="project-card-head">
                <div>
                  <h3><?= e($project['name']) ?></h3>
                  <span class="project-subtitle"><?= e($project['project_code']) ?> · <?= e(status_label($project['category'])) ?></span>
                </div>
                <span class="badge <?= status_badge_class($project['status']) ?>"><?= e(status_label($project['status'])) ?></span>
              </div>
              
              <div class="project-workflow" title="Status: <?= e(status_label($project['status'])) ?>">
                <?php foreach ($activeWorkflow as $status => $label): ?>
                  <span class="<?= $project['status'] === $status ? 'active' : '' ?>" title="<?= e($label) ?>"></span>
                <?php endforeach; ?>
              </div>
              
              <div class="project-info-grid">
                <div class="info-chip"><i class="ti ti-user"></i> <span>Assigned:</span> <strong><?= e($project['assigned_name'] ?: 'Unassigned') ?></strong></div>
                <div class="info-chip"><i class="ti ti-flag-2"></i> <span>Priority:</span> <strong><?= e(ucfirst($project['priority'])) ?></strong></div>
                <div class="info-chip"><i class="ti ti-calendar"></i> <span>Start:</span> <strong><?= e(format_date($project['start_date'])) ?></strong></div>
                <div class="info-chip"><i class="ti ti-calendar-due"></i> <span>Delivery:</span> <strong class="<?= $isOverdue ? 'danger-text' : '' ?>"><?= e(format_date($project['expected_delivery'])) ?></strong></div>
              </div>
              
              <div class="project-progress">
                <div class="progress"><div class="progress-fill" style="width:<?= (int) $project['completion_percentage'] ?>%"></div></div>
                <strong class="progress-percent"><?= (int) $project['completion_percentage'] ?>%</strong>
              </div>
              
              <div class="project-finance-panel">
                <div class="finance-item">
                  <span>Project Value</span>
                  <strong><?= e(format_money($project['selling_price'])) ?></strong>
                </div>
                <div class="finance-item">
                  <span>Advance</span>
                  <strong><?= e(format_money($project['advance_amount'])) ?></strong>
                </div>
                <div class="finance-item">
                  <span>Received</span>
                  <strong class="success-text"><?= e(format_money($project['received_amount'])) ?></strong>
                </div>
                <div class="finance-item">
                  <span>Balance</span>
                  <strong class="<?= $balance > 0 ? 'danger-text' : 'success-text' ?>"><?= e(format_money($balance)) ?></strong>
                </div>
              </div>
              
              <div class="project-card-actions">
                <a class="btn btn-outline btn-sm" href="<?= $withClient(['project_q' => $project['project_code']]) ?>"><i class="ti ti-folder-open"></i> Open Project</a>
                <button
                  class="btn btn-outline btn-sm project-edit-btn"
                  type="button"
                  data-open="projectEditModal"
                  data-project-id="<?= (int) $project['id'] ?>"
                  data-name="<?= e($project['name']) ?>"
                  data-category="<?= e($project['category']) ?>"
                  data-status="<?= e($project['status']) ?>"
                  data-priority="<?= e($project['priority']) ?>"
                  data-assigned="<?= e((string) ($project['assigned_employee_id'] ?? '')) ?>"
                  data-delivery="<?= e($project['expected_delivery'] ?? '') ?>"
                  data-progress="<?= (int) $project['completion_percentage'] ?>"
                  data-cost="<?= (float) $project['estimated_cost'] ?>"
                  data-price="<?= (float) $project['selling_price'] ?>"
                  data-advance="<?= (float) $project['advance'] ?>"
                  data-balance="<?= (float) $project['balance'] ?>"
                  data-description="<?= e($project['description'] ?? '') ?>">
                  <i class="ti ti-edit"></i> Edit
                </button>
                <button class="btn btn-outline btn-sm crud-delete" type="button" data-entity="project" data-id="<?= (int)$project['id'] ?>" data-label="<?= e($project['name']) ?>"><i class="ti ti-trash"></i> Delete</button>
                <button class="btn btn-primary btn-sm record-payment-btn" type="button" data-open="paymentModal" data-client-id="<?= $selectedClientId ?>" data-project-id="<?= (int) $project['id'] ?>"><i class="ti ti-cash"></i> Record Payment</button>
              </div>
            </article>
          <?php endforeach; ?>
          <?php if (empty($projects)): ?>
            <div class="empty-panel">No projects found for this client.</div>
          <?php endif; ?>
        </div>
      </section>

      <section class="workspace-two-col">
        <div class="workspace-section">
          <div class="section-toolbar compact">
            <div>
              <h2>Payments</h2>
              <p>Balance = Project Value - Received Amount.</p>
            </div>
            <button class="btn btn-primary btn-sm record-payment-btn" type="button" data-open="paymentModal" data-client-id="<?= $selectedClientId ?>"><i class="ti ti-plus"></i> Record Payment</button>
          </div>
          <div class="payment-summary-grid">
            <div><span>Total Project Value</span><strong><?= e(format_money($summary['total_business'] ?? 0)) ?></strong></div>
            <div><span>Advance Received</span><strong><?= e(format_money($summary['advance_received'] ?? 0)) ?></strong></div>
            <div><span>Amount Received</span><strong class="success-text"><?= e(format_money($summary['received_amount'] ?? 0)) ?></strong></div>
            <div><span>Outstanding Balance</span><strong class="danger-text"><?= e(format_money($summary['pending_balance'] ?? 0)) ?></strong></div>
            <div><span>Last Payment</span><strong><?= e($lastPayment ? format_date($lastPayment['payment_date']) : 'No payments') ?></strong></div>
            <div><span>Next Due Date</span><strong class="<?= $nextDue && $nextDue['due_date'] < date('Y-m-d') ? 'danger-text' : '' ?>"><?= e($nextDue ? format_date($nextDue['due_date']) : 'No due date') ?></strong></div>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Transaction ID</th><th>Notes</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach ($payments as $payment): ?>
                  <tr>
                    <td><?= e(format_date($payment['payment_date'])) ?></td>
                    <td><?= e(format_money($payment['amount'])) ?></td>
                    <td><?= e(status_label($payment['payment_method'])) ?></td>
                    <td><?= e($payment['transaction_id'] ?: '-') ?></td>
                    <td><?= e($payment['notes'] ?: $payment['project_name'] ?: '-') ?></td>
                    <td><div class="table-actions">
                      <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="payment" data-id="<?= (int)$payment['id'] ?>" data-record="<?= e(json_encode($payment)) ?>"><i class="ti ti-edit"></i></button>
                      <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="payment" data-id="<?= (int)$payment['id'] ?>" data-label="this payment"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
                    </div></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?><tr><td colspan="6" class="empty-cell">No payment history yet.</td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="workspace-section">
          <div class="section-toolbar compact">
            <div>
              <h2>Activity Timeline</h2>
              <p>Newest activity first.</p>
            </div>
          </div>
          <div class="activity-list">
            <div class="activity-item">
              <span class="activity-dot"></span>
              <div><strong>Client Created</strong><small><?= e(format_date($selectedClient['created_at'])) ?></small></div>
            </div>
            <?php foreach ($timeline as $event): ?>
              <div class="activity-item">
                <span class="activity-dot"></span>
                <div><strong><?= e($event['title']) ?></strong><small><?= e($event['detail']) ?> · <?= e(format_date($event['created_at'])) ?></small></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="workspace-two-col">
        <div class="workspace-section">
          <div class="section-toolbar compact">
            <div><h2>Quotations</h2><p>Quote number, amount, and current status.</p></div>
            <button class="btn btn-outline btn-sm" type="button" data-open="quotationModal" id="createQuoteForClient"><i class="ti ti-file-plus"></i> Create Quotation</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Quote Number</th><th>Date</th><th>Amount</th><th>Status</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($quotations as $quote): ?>
                  <tr>
                    <td><?= e($quote['quote_number']) ?></td>
                    <td><?= e(format_date($quote['created_at'])) ?></td>
                    <td><?= e(format_money($quote['total_amount'])) ?></td>
                    <td><span class="badge <?= status_badge_class($quote['status']) ?>"><?= e(status_label($quote['status'])) ?></span></td>
                    <td><div class="table-actions">
                      <a class="btn btn-ghost btn-sm" href="<?= url('documents/quotation/' . $quote['id'] . '/pdf') ?>" target="_blank"><i class="ti ti-download"></i> PDF</a>
                      <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="quotation" data-id="<?= (int)$quote['id'] ?>" data-record="<?= e(json_encode($quote)) ?>"><i class="ti ti-edit"></i></button>
                      <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="quotation" data-id="<?= (int)$quote['id'] ?>" data-label="<?= e($quote['quote_number']) ?>"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
                    </div></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($quotations)): ?><tr><td colspan="5" class="empty-cell">No quotations yet.</td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="workspace-section">
          <div class="section-toolbar compact">
            <div><h2>Invoices</h2><p>Invoice balance and paid status.</p></div>
            <button class="btn btn-outline btn-sm" type="button" data-open="invoiceModal" id="generateInvoiceForClient"><i class="ti ti-receipt"></i> Generate Invoice</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Invoice Number</th><th>Date</th><th>Amount</th><th>Paid</th><th>Balance</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($invoices as $invoice): ?>
                  <tr>
                    <td><?= e($invoice['invoice_number']) ?></td>
                    <td><?= e(format_date($invoice['invoice_date'])) ?></td>
                    <td><?= e(format_money($invoice['total_amount'])) ?></td>
                    <td><?= e(format_money($invoice['received_amount'])) ?></td>
                    <td class="<?= (float)$invoice['pending_amount'] > 0 ? 'danger-text' : '' ?>"><?= e(format_money($invoice['pending_amount'])) ?></td>
                    <td><div class="table-actions">
                      <a class="btn btn-ghost btn-sm" href="<?= url('documents/invoice/' . $invoice['id'] . '/pdf') ?>" target="_blank"><i class="ti ti-download"></i> PDF</a>
                      <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="invoice" data-id="<?= (int)$invoice['id'] ?>" data-record="<?= e(json_encode($invoice)) ?>"><i class="ti ti-edit"></i></button>
                      <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="invoice" data-id="<?= (int)$invoice['id'] ?>" data-label="<?= e($invoice['invoice_number']) ?>"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
                    </div></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($invoices)): ?><tr><td colspan="6" class="empty-cell">No invoices yet.</td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section class="workspace-two-col">
        <div class="workspace-section">
          <div class="section-toolbar compact">
            <div><h2>Project Documents</h2><p>AI, PSD, CDR, PDF, JPG, PNG, MP4, ZIP and more.</p></div>
          </div>
          <form id="uploadForm" class="file-upload-strip" enctype="multipart/form-data">
            <input type="hidden" name="client_id" value="<?= $selectedClientId ?>">
            <select class="form-control" name="project_id">
              <option value="">Client level file</option>
              <?php foreach ($projects as $project): ?>
                <option value="<?= (int) $project['id'] ?>"><?= e($project['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <input class="form-control" type="file" name="file" accept=".ai,.psd,.cdr,.pdf,.jpg,.jpeg,.png,.mp4,.zip" required>
            <button class="btn btn-primary" type="submit"><i class="ti ti-upload"></i> Upload</button>
          </form>
          <div class="file-list">
            <?php foreach ($files as $file): ?>
              <div class="file-row">
                <div>
                  <strong><i class="ti <?= e(\App\Models\FileModel::iconClass($file['extension'] ?? '')) ?>"></i><?= e($file['original_name']) ?></strong>
                  <span><?= e($file['project_name'] ?: 'Client file') ?> · <?= e(strtoupper($file['extension'] ?? 'file')) ?></span>
                </div>
                <div class="file-actions">
                  <a class="btn btn-ghost btn-sm" target="_blank" href="<?= url('files/download/' . $file['id']) ?>">Preview</a>
                  <a class="btn btn-ghost btn-sm" href="<?= url('files/download/' . $file['id']) ?>">Download</a>
                  <button class="btn btn-ghost btn-sm delete-file" type="button" data-id="<?= (int) $file['id'] ?>">Delete</button>
                </div>
              </div>
            <?php endforeach; ?>
            <?php if (empty($files)): ?><div class="empty-panel">No project documents uploaded yet.</div><?php endif; ?>
          </div>
        </div>

        <div class="workspace-section">
          <div class="section-toolbar compact">
            <div><h2>Notes</h2><p>Internal, client, meeting, and revision notes.</p></div>
          </div>
          <form class="ajax-form notes-form" data-endpoint="/api/clients/<?= $selectedClientId ?>/notes" data-method="POST">
            <label class="form-label">Client Notes</label>
            <textarea class="form-control rich-note" name="notes" rows="5" placeholder="Internal notes, client notes, meeting notes, revision notes..."><?= e($selectedClient['notes'] ?? '') ?></textarea>
            <button class="btn btn-primary btn-sm" type="submit">Save Notes</button>
          </form>
          <div class="note-history">
            <?php foreach ($notes as $note): ?>
              <div class="note-item">
                <strong><?= e($note['project_name']) ?></strong>
                <span><?= e($note['comment']) ?></span>
                <small><?= e($note['user_name'] ?: 'Team') ?> · <?= e(format_date($note['created_at'])) ?></small>
              </div>
            <?php endforeach; ?>
            <?php if (empty($notes)): ?><div class="empty-panel">No project notes yet.</div><?php endif; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>
  </main>
</div>

<script>
  window.__PROJECTS_CLIENT_ID__ = <?= $selectedClientId ?>;
</script>

<?php if ($selectedClient): ?>
  <div class="modal-overlay" id="projectEditModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-title">Edit Project</div>
        <button class="icon-btn" type="button" data-close="projectEditModal"><i class="ti ti-x"></i></button>
      </div>
      <form id="projectEditForm" class="ajax-form" data-endpoint="/api/projects/0/update" data-method="POST">
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group"><label class="form-label">Project Name</label><input class="form-control" name="name" required></div>
            <div class="form-group"><label class="form-label">Service Type</label><select class="form-control" name="category"><?php foreach (config('app.service_categories') as $service): ?><option value="<?= e($service) ?>"><?= e(status_label($service)) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label">Status</label><select class="form-control" name="status"><?php foreach (config('app.project_statuses') as $status): ?><option value="<?= e($status) ?>"><?= e(status_label($status)) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label">Priority</label><select class="form-control" name="priority"><?php foreach (['low','medium','high','urgent'] as $priority): ?><option value="<?= e($priority) ?>"><?= e(ucfirst($priority)) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label">Assigned Employee</label><select class="form-control" name="assigned_employee_id"><option value="">Unassigned</option><?php foreach ($employees as $employee): ?><option value="<?= (int)$employee['id'] ?>"><?= e($employee['name']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label">Delivery Date</label><input class="form-control" type="date" name="expected_delivery"></div>
            <div class="form-group"><label class="form-label">Progress %</label><input class="form-control" type="number" name="completion_percentage" min="0" max="100"></div>
            <div class="form-group"><label class="form-label">Estimated Cost (₹)</label><input class="form-control" type="number" step="0.01" name="estimated_cost"></div>
            <div class="form-group"><label class="form-label">Selling Price (₹)</label><input class="form-control" type="number" step="0.01" name="selling_price"></div>
            <div class="form-group"><label class="form-label">Advance (₹)</label><input class="form-control" type="number" step="0.01" name="advance"></div>
            <div class="form-group"><label class="form-label">Balance (₹)</label><input class="form-control" type="number" step="0.01" name="balance"></div>
            <div class="form-group" style="grid-column:1/-1"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-close="projectEditModal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Project</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="clientEditModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-title">Edit Client</div>
        <button class="icon-btn" type="button" data-close="clientEditModal"><i class="ti ti-x"></i></button>
      </div>
      <form class="ajax-form" data-endpoint="/api/clients/<?= $selectedClientId ?>/update" data-method="POST">
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group"><label class="form-label">Company Name</label><input class="form-control" name="company_name" value="<?= e($selectedClient['company_name']) ?>" required></div>
            <div class="form-group"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person" value="<?= e($selectedClient['contact_person'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($selectedClient['phone'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= e($selectedClient['email'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">GST</label><input class="form-control" name="gst_number" value="<?= e($selectedClient['gst_number'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">Status</label><select class="form-control" name="status"><?php foreach (['active','follow_up','lead','inactive'] as $status): ?><option value="<?= e($status) ?>" <?= $selectedClient['status'] === $status ? 'selected' : '' ?>><?= e(status_label($status)) ?></option><?php endforeach; ?></select></div>
            <div class="form-group" style="grid-column:1/-1"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"><?= e($selectedClient['address'] ?? '') ?></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-close="clientEditModal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Client</button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>
