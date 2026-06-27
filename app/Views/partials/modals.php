<?php
$clients = db_safe(fn () => \App\Models\Client::dropdown(), []);
$employees = db_safe(fn () => \App\Models\Employee::dropdown(), []);
$projectsLookup = db_safe(fn () => \App\Models\Project::all([], 1, 200), []);
$invoicesLookup = db_safe(fn () => \App\Models\Invoice::all(1, 200), []);
?>
<div class="modal-overlay" id="clientModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">Add New Client</div><button class="icon-btn" type="button" data-close="clientModal"><i class="ti ti-x"></i></button></div>
    <form id="clientForm" class="ajax-form" data-endpoint="/api/clients" data-method="POST">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group"><label class="form-label">Company Name *</label><input class="form-control" name="company_name" required placeholder="Acme Corp Pvt Ltd"></div>
          <div class="form-group"><label class="form-label">Industry</label><select class="form-control" name="industry"><option value="">Select industry</option><option>FMCG</option><option>E-Commerce</option><option>Media</option><option>Technology</option></select></div>
          <div class="form-group"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person" placeholder="Full name"></div>
          <div class="form-group"><label class="form-label">Phone</label><input class="form-control" name="phone" placeholder="+91 98400 00000"></div>
          <div class="form-group"><label class="form-label">Email</label><input class="form-control" name="email" type="email" placeholder="contact@company.com"></div>
          <div class="form-group"><label class="form-label">Website</label><input class="form-control" name="website" placeholder="www.company.com"></div>
          <div class="form-group"><label class="form-label">GST Number</label><input class="form-control" name="gst_number" placeholder="33AABCX0000X1Z0"></div>
          <div class="form-group"><label class="form-label">Tags</label><input class="form-control" name="tags" placeholder="vip, retainer…"></div>
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="clientModal">Cancel</button><button type="submit" class="btn btn-primary">Save Client</button></div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="projectModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">New Project</div><button class="icon-btn" type="button" data-close="projectModal"><i class="ti ti-x"></i></button></div>
    <form id="projectForm" class="ajax-form" data-endpoint="/api/projects" data-method="POST">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group"><label class="form-label">Project Name *</label><input class="form-control" name="name" required></div>
          <div class="form-group"><label class="form-label">Client *</label><select class="form-control" name="client_id" required><option value="">Select client</option><?php foreach ($clients as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['company_name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Category</label><select class="form-control" name="category"><?php foreach (config('app.service_categories') as $cat): ?><option value="<?= e($cat) ?>"><?= e(status_label($cat)) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Priority</label><select class="form-control" name="priority"><option value="medium">Medium</option><option value="high">High</option><option value="low">Low</option><option value="urgent">Urgent</option></select></div>
          <div class="form-group"><label class="form-label">Start Date</label><input type="date" class="form-control" name="start_date"></div>
          <div class="form-group"><label class="form-label">Expected Delivery</label><input type="date" class="form-control" name="expected_delivery"></div>
          <div class="form-group"><label class="form-label">Estimated Cost (₹)</label><input class="form-control" name="estimated_cost" type="number" step="0.01"></div>
          <div class="form-group"><label class="form-label">Selling Price (₹)</label><input class="form-control" name="selling_price" type="number" step="0.01"></div>
          <div class="form-group"><label class="form-label">Advance (₹)</label><input class="form-control" name="advance" type="number" step="0.01" placeholder="0.00"></div>
          <div class="form-group"><label class="form-label">Balance (₹)</label><input class="form-control" name="balance" type="number" step="0.01" placeholder="0.00"></div>
          <div class="form-group"><label class="form-label">Assigned Employee</label><select class="form-control" name="assigned_employee_id"><option value="">Unassigned</option><?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>"><?= e($e['name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Status</label><select class="form-control" name="status"><?php foreach (config('app.project_statuses') as $st): ?><option value="<?= e($st) ?>"><?= e(status_label($st)) ?></option><?php endforeach; ?></select></div>
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="projectModal">Cancel</button><button type="submit" class="btn btn-primary">Create Project</button></div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="paymentModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">Record Payment</div><button class="icon-btn" type="button" data-close="paymentModal"><i class="ti ti-x"></i></button></div>
    <form class="ajax-form" data-endpoint="/api/payments" data-method="POST">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group"><label class="form-label">Client *</label><select class="form-control" name="client_id" required><option value="">Select</option><?php foreach ($clients as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['company_name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Amount (Rs) *</label><input class="form-control" name="amount" type="number" step="0.01" required></div>
          <div class="form-group"><label class="form-label">Project</label><select class="form-control" name="project_id"><option value="">No project</option><?php foreach ($projectsLookup as $project): ?><option value="<?= (int)$project['id'] ?>" data-client="<?= (int)$project['client_id'] ?>"><?= e($project['project_code'] . ' - ' . $project['name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Invoice</label><select class="form-control" name="invoice_id"><option value="">Unallocated payment</option><?php foreach ($invoicesLookup as $invoice): ?><option value="<?= (int)$invoice['id'] ?>" data-client="<?= (int)$invoice['client_id'] ?>" data-project="<?= (int)($invoice['project_id'] ?? 0) ?>" data-pending="<?= e((string)$invoice['pending_amount']) ?>"><?= e($invoice['invoice_number'] . ' - ' . $invoice['client_name'] . ' - Due ' . format_money($invoice['pending_amount'])) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Payment Stage</label><select class="form-control" name="payment_stage"><option value="advance">Advance</option><option value="25">25%</option><option value="50">50%</option><option value="75">75%</option><option value="final">Final Payment</option><option value="other">Other</option></select></div>
          <div class="form-group"><label class="form-label">Payment Method</label><select class="form-control" name="payment_method"><option value="upi">UPI</option><option value="neft">NEFT</option><option value="rtgs">RTGS</option><option value="cash">Cash</option><option value="cheque">Cheque</option><option value="card">Card</option><option value="other">Other</option></select></div>
          <div class="form-group"><label class="form-label">Transaction ID</label><input class="form-control" name="transaction_id"></div>
          <div class="form-group"><label class="form-label">Payment Date</label><input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d') ?>"></div>
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2" placeholder="Milestone, reference, or internal note"></textarea></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="paymentModal">Cancel</button><button type="submit" class="btn btn-primary">Record Payment</button></div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="expenseModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">Add Expense</div><button class="icon-btn" type="button" data-close="expenseModal"><i class="ti ti-x"></i></button></div>
    <form class="ajax-form" data-endpoint="/api/expenses" data-method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group"><label class="form-label">Category</label><select class="form-control" name="category"><?php foreach (config('app.expense_categories') as $cat): ?><option value="<?= e($cat) ?>"><?= e(status_label($cat)) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Amount (₹)</label><input class="form-control" name="amount" type="number" step="0.01" required></div>
          <div class="form-group"><label class="form-label">Date</label><input type="date" class="form-control" name="expense_date" value="<?= date('Y-m-d') ?>"></div>
          <div class="form-group"><label class="form-label">Paid Via</label><select class="form-control" name="paid_via"><option value="bank_transfer">Bank Transfer</option><option value="upi">UPI</option><option value="cash">Cash</option><option value="credit_card">Credit Card</option></select></div>
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">Description</label><input class="form-control" name="description" required></div>
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">Receipt</label><input type="file" class="form-control" name="receipt" accept=".pdf,.png,.jpg,.jpeg"></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="expenseModal">Cancel</button><button type="submit" class="btn btn-primary">Save Expense</button></div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="quotationModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">New Quotation</div><button class="icon-btn" type="button" data-close="quotationModal"><i class="ti ti-x"></i></button></div>
    <form class="ajax-form" data-endpoint="/api/quotations" data-method="POST">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group"><label class="form-label">Client *</label><select class="form-control" name="client_id" required><option value="">Select</option><?php foreach ($clients as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['company_name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Subject</label><input class="form-control" name="subject"></div>
          <div class="form-group"><label class="form-label">Amount (₹)</label><input class="form-control" name="amount" type="number" step="0.01"></div>
          <div class="form-group"><label class="form-label">Valid Until</label><input type="date" class="form-control" name="valid_until"></div>
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">Terms</label><textarea class="form-control" name="terms" rows="3"></textarea></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="quotationModal">Cancel</button><button type="submit" class="btn btn-primary">Save Quotation</button></div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="taskModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">New Task</div><button type="button" class="icon-btn" data-close="taskModal"><i class="ti ti-x"></i></button></div>
    <form class="ajax-form" data-endpoint="/api/tasks" data-method="POST">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">Title *</label><input class="form-control" name="title" required></div>
          <div class="form-group"><label class="form-label">Project</label><select class="form-control" name="project_id"><option value="">General</option><?php foreach (\App\Models\Project::all([],1,100) as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Assign To</label><select class="form-control" name="assigned_to"><option value="">Unassigned</option><?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>"><?= e($e['name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Priority</label><select class="form-control" name="priority"><option value="medium">Medium</option><option value="high">High</option><option value="low">Low</option><option value="urgent">Urgent</option></select></div>
          <div class="form-group"><label class="form-label">Due Date</label><input type="date" class="form-control" name="due_date"></div>
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="taskModal">Cancel</button><button type="submit" class="btn btn-primary">Create Task</button></div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="employeeModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">Add Employee</div><button type="button" class="icon-btn" data-close="employeeModal"><i class="ti ti-x"></i></button></div>
    <form class="ajax-form" data-endpoint="/api/employees" data-method="POST">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group"><label class="form-label">Name *</label><input class="form-control" name="name" required></div>
          <div class="form-group"><label class="form-label">Designation</label><input class="form-control" name="designation"></div>
          <div class="form-group"><label class="form-label">Email</label><input class="form-control" name="email" type="email"></div>
          <div class="form-group"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
          <div class="form-group"><label class="form-label">Skills (comma separated)</label><input class="form-control" name="skills" placeholder="Branding, Web Dev"></div>
          <div class="form-group"><label class="form-label">Salary (₹)</label><input class="form-control" name="salary" type="number"></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="employeeModal">Cancel</button><button type="submit" class="btn btn-primary">Add Employee</button></div>
    </form>
  </div>
</div>

<div id="toast" class="toast" style="display:none"></div>

<div class="modal-overlay" id="crudEditModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="crudEditTitle">Edit Record</div>
      <button type="button" class="icon-btn" data-close="crudEditModal"><i class="ti ti-x"></i></button>
    </div>
    <form id="crudEditForm" class="ajax-form" data-endpoint="" data-method="POST">
      <div class="modal-body"><div class="form-grid" id="crudEditFields"></div></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-close="crudEditModal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
window.CRUD_LOOKUPS = <?= json_encode([
    'clients' => array_map(fn($row) => ['value' => (string)$row['id'], 'label' => $row['company_name']], $clients),
    'employees' => array_map(fn($row) => ['value' => (string)$row['id'], 'label' => $row['name']], $employees),
    'projects' => array_map(fn($row) => ['value' => (string)$row['id'], 'label' => $row['name']], $projectsLookup),
    'invoices' => array_map(fn($row) => ['value' => (string)$row['id'], 'label' => $row['invoice_number']], $invoicesLookup),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<div class="modal-overlay" id="invoiceModal">
  <div class="modal" style="width:650px">
    <div class="modal-header"><div class="modal-title">Generate Invoice</div><button type="button" class="icon-btn" data-close="invoiceModal"><i class="ti ti-x"></i></button></div>
    <form id="invoiceForm" class="ajax-form" data-endpoint="/api/invoices" data-method="POST">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group"><label class="form-label">Client *</label><select class="form-control" name="client_id" required><option value="">Select</option><?php foreach ($clients as $c): ?><option value="<?= $c['id'] ?>" data-address="<?= e($c['address']) ?>"><?= e($c['company_name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Invoice Date</label><input type="date" class="form-control" name="invoice_date" value="<?= date('Y-m-d') ?>"></div>
          <div class="form-group"><label class="form-label">Due Date</label><input type="date" class="form-control" name="due_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>"></div>
          <div class="form-group"><label class="form-label">GST Rate</label><select class="form-control" name="gst_rate"><option value="18">18%</option><option value="12">12%</option><option value="5">5%</option><option value="0">0%</option></select></div>
          <div class="form-group"><label class="form-label">Discount (%)</label><input class="form-control" name="discount_percent" value="0" type="number" step="0.01"></div>
          <div class="form-group" style="grid-column:1/-1">
            <label class="form-label">Billing Address (Optional override)</label>
            <textarea class="form-control" name="billing_address" id="invoiceBillingAddress" rows="2" placeholder="Manual billing address override..."></textarea>
          </div>
        </div>
        <div style="margin-top:16px" id="invoiceLineItems">
          <div style="font-size:13px;font-weight:600;margin-bottom:10px">Line Items</div>
          <div class="invoice-line form-grid" style="margin-bottom:8px">
            <div class="form-group"><input class="form-control" name="item_service[]" placeholder="Service name" required></div>
            <div class="form-group"><input class="form-control" name="item_qty[]" type="number" value="1" min="1" step="0.01"></div>
            <div class="form-group"><input class="form-control" name="item_rate[]" type="number" placeholder="Rate" step="0.01" required></div>
          </div>
          <button type="button" class="btn btn-ghost btn-sm" id="addInvoiceLine"><i class="ti ti-plus"></i> Add line item</button>
        </div>
        <input type="hidden" name="items" id="invoiceItemsJson" value="[]">
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="invoiceModal">Cancel</button><button type="submit" class="btn btn-primary">Generate Invoice</button></div>
    </form>
  </div>
</div>
