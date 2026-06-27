<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Client;
use App\Models\Communication;
use App\Models\Dashboard;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\FileModel;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Search;
use App\Models\Sequence;
use App\Models\Task;
use App\Services\BackupService;

class ApiController extends Controller
{
    public function search(): void
    {
        $q = trim((string) $this->input('q'));
        if (strlen($q) < 2) {
            $this->json(['success' => true, 'results' => []]);
        }
        $results = Search::global($q);
        $flat = [];
        foreach ($results as $group) {
            foreach ($group as $item) {
                $flat[] = $item;
            }
        }
        $this->json(['success' => true, 'results' => $flat]);
    }

    public function notifications(): void
    {
        $this->json([
            'success' => true,
            'data' => Dashboard::notifications(),
            'unread' => Dashboard::unreadCount(),
        ]);
    }

    public function markNotificationsRead(): void
    {
        Database::update('notifications', ['is_read' => 1], 'user_id = ?', [Auth::id()]);
        $this->json(['success' => true]);
    }

    // Clients
    public function storeClient(): void
    {
        $data = $this->validate([
            'company_name' => 'required',
            'email' => 'email',
        ]);
        $id = Client::create([
            'company_name' => $data['company_name'],
            'contact_person' => $this->input('contact_person'),
            'phone' => $this->input('phone'),
            'email' => $data['email'] ?: null,
            'address' => $this->input('address'),
            'gst_number' => $this->input('gst_number'),
            'industry' => $this->input('industry'),
            'website' => $this->input('website'),
            'notes' => $this->input('notes'),
            'tags' => $this->input('tags'),
            'status' => $this->input('status', 'active'),
            'created_by' => Auth::id(),
        ]);
        ActivityLog::write('create', 'client', $id, 'Client created: ' . $data['company_name']);
        $this->json(['success' => true, 'id' => $id, 'message' => 'Client saved successfully.']);
    }

    public function updateClient(string $id): void
    {
        $data = $this->validate(['company_name' => 'required', 'email' => 'email']);
        Client::update((int) $id, [
            'company_name' => $data['company_name'],
            'contact_person' => $this->input('contact_person'),
            'phone' => $this->input('phone'),
            'email' => $data['email'] ?: null,
            'address' => $this->input('address'),
            'gst_number' => $this->input('gst_number'),
            'industry' => $this->input('industry'),
            'website' => $this->input('website'),
            'notes' => $this->input('notes'),
            'tags' => $this->input('tags'),
            'status' => $this->input('status', 'active'),
        ]);
        ActivityLog::write('update', 'client', (int) $id, 'Client updated');
        $this->json(['success' => true, 'message' => 'Client updated.']);
    }

    public function updateClientNotes(string $id): void
    {
        Client::update((int) $id, [
            'notes' => $this->input('notes', ''),
        ]);
        ActivityLog::write('update', 'client', (int) $id, 'Client notes updated');
        $this->json(['success' => true, 'message' => 'Notes saved.']);
    }

    public function deleteClient(string $id): void
    {
        $clientId = (int) $id;
        $linked = Database::fetch(
            'SELECT (SELECT COUNT(*) FROM projects WHERE client_id = ?) +
                    (SELECT COUNT(*) FROM invoices WHERE client_id = ?) +
                    (SELECT COUNT(*) FROM quotations WHERE client_id = ?) +
                    (SELECT COUNT(*) FROM payments WHERE client_id = ?) AS total',
            [$clientId, $clientId, $clientId, $clientId]
        );
        if ((int) ($linked['total'] ?? 0) > 0) {
            $this->json(['success' => false, 'message' => 'This client has linked projects or financial records. Remove those first.'], 409);
        }
        Database::delete('clients', 'id = ?', [$clientId]);
        ActivityLog::write('delete', 'client', $clientId, 'Client deleted');
        $this->json(['success' => true, 'message' => 'Client deleted.']);
    }

    // Projects
    public function storeProject(): void
    {
        $data = $this->validate(['name' => 'required', 'client_id' => 'required']);
        $prefix = Database::fetch('SELECT project_prefix FROM company_settings LIMIT 1')['project_prefix'] ?? 'PRJ';
        $code = Sequence::next('project', $prefix);
        $cost = (float) $this->input('estimated_cost', 0);
        $price = (float) $this->input('selling_price', 0);
        $advance = (float) $this->input('advance', 0);
        $balance = (float) $this->input('balance', 0);
        $id = Project::create([
            'project_code' => $code,
            'client_id' => (int) $data['client_id'],
            'name' => $data['name'],
            'category' => $this->input('category', 'branding'),
            'description' => $this->input('description'),
            'start_date' => $this->input('start_date') ?: null,
            'expected_delivery' => $this->input('expected_delivery') ?: null,
            'priority' => $this->input('priority', 'medium'),
            'assigned_employee_id' => $this->input('assigned_employee_id') ?: null,
            'estimated_cost' => $cost,
            'selling_price' => $price,
            'advance' => $advance,
            'balance' => $balance,
            'status' => $this->input('status', 'lead'),
            'completion_percentage' => (int) $this->input('completion_percentage', 0),
            'created_by' => Auth::id(),
        ]);
        ActivityLog::write('create', 'project', $id, 'Project created: ' . $data['name']);
        $this->json(['success' => true, 'id' => $id, 'project_code' => $code, 'message' => 'Project created.']);
    }

    public function updateProjectStatus(string $id): void
    {
        $status = (string) $this->input('status');
        $progress = (int) $this->input('completion_percentage', 0);
        Project::update((int) $id, [
            'status' => $status,
            'completion_percentage' => $progress,
        ]);
        $this->json(['success' => true, 'message' => 'Project status updated.']);
    }

    public function projectDetails(string $id): void
    {
        $projectId = (int) $id;
        $project = Project::find($projectId);
        if (!$project) {
            $this->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $tasks = Database::fetchAll(
            "SELECT t.*, e.name as assignee_name
             FROM tasks t
             LEFT JOIN employees e ON e.id = t.assigned_to
             WHERE t.project_id = ?
             ORDER BY FIELD(t.status,'todo','in_progress','review','done'), t.due_date ASC",
            [$projectId]
        );

        $taskSummary = [
            'todo' => 0,
            'in_progress' => 0,
            'review' => 0,
            'done' => 0,
            'overdue' => 0,
        ];
        foreach ($tasks as $task) {
            if (isset($taskSummary[$task['status']])) {
                $taskSummary[$task['status']]++;
            }
            if (
                !empty($task['due_date'])
                && $task['due_date'] < date('Y-m-d')
                && $task['status'] !== 'done'
            ) {
                $taskSummary['overdue']++;
            }
        }

        $files = Database::fetchAll(
            "SELECT f.* FROM files f WHERE f.project_id = ? ORDER BY f.created_at DESC LIMIT 30",
            [$projectId]
        );

        $invoices = Database::fetchAll(
            "SELECT id, invoice_number, total_amount, pending_amount, status, invoice_date
             FROM invoices WHERE project_id = ? ORDER BY invoice_date DESC",
            [$projectId]
        );

        $payments = Database::fetchAll(
            "SELECT id, amount, payment_method, payment_date, transaction_id
             FROM payments WHERE project_id = ? ORDER BY payment_date DESC LIMIT 20",
            [$projectId]
        );

        $comments = Database::fetchAll(
            "SELECT pc.*, u.name as user_name
             FROM project_comments pc
             LEFT JOIN users u ON u.id = pc.user_id
             WHERE pc.project_id = ?
             ORDER BY pc.created_at DESC LIMIT 20",
            [$projectId]
        );

        $totals = Database::fetch(
            "SELECT
                COALESCE(SUM(total_amount),0) as billed,
                COALESCE(SUM(total_amount - pending_amount),0) as received,
                COALESCE(SUM(pending_amount),0) as pending
             FROM invoices WHERE project_id = ?",
            [$projectId]
        ) ?: ['billed' => 0, 'received' => 0, 'pending' => 0];

        $this->json([
            'success' => true,
            'project' => $project,
            'tasks' => $tasks,
            'task_summary' => $taskSummary,
            'files' => $files,
            'invoices' => $invoices,
            'payments' => $payments,
            'comments' => $comments,
            'totals' => $totals,
        ]);
    }

    public function updateProject(string $id): void
    {
        $projectId = (int) $id;
        $payload = array_filter([
            'name' => $this->input('name'),
            'category' => $this->input('category'),
            'description' => $this->input('description'),
            'priority' => $this->input('priority'),
            'assigned_employee_id' => $this->input('assigned_employee_id') !== null && $this->input('assigned_employee_id') !== '' ? (int) $this->input('assigned_employee_id') : null,
            'status' => $this->input('status'),
            'completion_percentage' => $this->input('completion_percentage') !== null ? (int) $this->input('completion_percentage') : null,
            'expected_delivery' => $this->input('expected_delivery') ?: null,
            'estimated_cost' => $this->input('estimated_cost') !== null && $this->input('estimated_cost') !== '' ? (float) $this->input('estimated_cost') : null,
            'selling_price' => $this->input('selling_price') !== null && $this->input('selling_price') !== '' ? (float) $this->input('selling_price') : null,
            'advance' => $this->input('advance') !== null && $this->input('advance') !== '' ? (float) $this->input('advance') : null,
            'balance' => $this->input('balance') !== null && $this->input('balance') !== '' ? (float) $this->input('balance') : null,
        ], fn($v) => $v !== null);

        if (!$payload) {
            $this->json(['success' => false, 'message' => 'Nothing to update.'], 422);
        }

        Project::update($projectId, $payload);
        ActivityLog::write('update', 'project', $projectId, 'Project updated');
        $this->json(['success' => true, 'message' => 'Project updated.']);
    }

    public function completeProject(string $id): void
    {
        $projectId = (int) $id;
        Project::update($projectId, [
            'status' => 'completed',
            'completion_percentage' => 100,
        ]);
        ActivityLog::write('update', 'project', $projectId, 'Project marked completed');
        $this->json(['success' => true, 'message' => 'Project marked as completed.']);
    }

    public function addProjectComment(string $id): void
    {
        $projectId = (int) $id;
        $comment = trim((string) $this->input('comment'));
        if ($comment === '') {
            $this->json(['success' => false, 'message' => 'Comment is required.'], 422);
        }
        Database::insert('project_comments', [
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'comment' => $comment,
        ]);
        ActivityLog::write('create', 'project_comment', $projectId, 'Project comment added');
        $this->json(['success' => true, 'message' => 'Update added.']);
    }

    public function deleteProject(string $id): void
    {
        $projectId = (int) $id;
        if (!Project::find($projectId)) {
            $this->json(['success' => false, 'message' => 'Project not found.'], 404);
        }
        Database::delete('projects', 'id = ?', [$projectId]);
        ActivityLog::write('delete', 'project', $projectId, 'Project deleted');
        $this->json(['success' => true, 'message' => 'Project deleted.']);
    }

    // Tasks
    public function storeTask(): void
    {
        $data = $this->validate(['title' => 'required']);
        $id = Task::create([
            'project_id' => $this->input('project_id') ?: null,
            'title' => $data['title'],
            'description' => $this->input('description'),
            'status' => $this->input('status', 'todo'),
            'priority' => $this->input('priority', 'medium'),
            'assigned_to' => $this->input('assigned_to') ?: null,
            'due_date' => $this->input('due_date') ?: null,
            'created_by' => Auth::id(),
        ]);
        $this->json(['success' => true, 'id' => $id, 'message' => 'Task created.']);
    }

    public function updateTaskStatus(string $id): void
    {
        Task::update((int) $id, ['status' => (string) $this->input('status')]);
        $this->json(['success' => true]);
    }

    public function updateTask(string $id): void
    {
        $data = $this->validate(['title' => 'required']);
        Task::update((int) $id, [
            'title' => $data['title'],
            'description' => $this->input('description'),
            'project_id' => $this->input('project_id') ?: null,
            'assigned_to' => $this->input('assigned_to') ?: null,
            'priority' => $this->input('priority', 'medium'),
            'status' => $this->input('status', 'todo'),
            'due_date' => $this->input('due_date') ?: null,
        ]);
        ActivityLog::write('update', 'task', (int) $id, 'Task updated');
        $this->json(['success' => true, 'message' => 'Task updated.']);
    }

    public function deleteTask(string $id): void
    {
        Database::delete('tasks', 'id = ?', [(int) $id]);
        ActivityLog::write('delete', 'task', (int) $id, 'Task deleted');
        $this->json(['success' => true, 'message' => 'Task deleted.']);
    }

    // Payments
    public function storePayment(): void
    {
        $data = $this->validate(['amount' => 'required', 'client_id' => 'required']);
        $amount = (float) $data['amount'];
        if ($amount <= 0) {
            $this->json(['success' => false, 'message' => 'Payment amount must be greater than zero.'], 422);
        }
        $invoiceId = $this->input('invoice_id') ? (int) $this->input('invoice_id') : null;
        $clientId = (int) $data['client_id'];
        $projectId = $this->input('project_id') ? (int) $this->input('project_id') : null;
        if ($invoiceId) {
            $invoice = $this->validatePaymentInvoice($invoiceId, $clientId, $amount);
            $projectId = $invoice['project_id'] ? (int) $invoice['project_id'] : $projectId;
        }
        $this->validatePaymentProject($projectId, $clientId);
        Database::beginTransaction();
        try {
            $id = Payment::create([
                'invoice_id' => $invoiceId,
                'client_id' => $clientId,
                'project_id' => $projectId,
                'amount' => $amount,
                'payment_stage' => $this->input('payment_stage', 'other'),
                'payment_method' => $this->input('payment_method', 'upi'),
                'transaction_id' => $this->input('transaction_id'),
                'payment_date' => $this->input('payment_date', date('Y-m-d')),
                'gst_included' => (int) ($this->input('gst_included', 1)),
                'notes' => $this->input('notes'),
                'created_by' => Auth::id(),
            ]);
            if ($invoiceId) {
                Payment::reconcileInvoice($invoiceId);
            }
            Client::recalculateBalance($clientId);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
        ActivityLog::write('create', 'payment', $id, 'Payment recorded: ' . format_money($amount));
        $this->json(['success' => true, 'id' => $id, 'message' => 'Payment recorded.']);
    }

    public function updatePayment(string $id): void
    {
        $paymentId = (int) $id;
        $payment = Database::fetch('SELECT * FROM payments WHERE id = ?', [$paymentId]);
        if (!$payment) {
            $this->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }
        $data = $this->validate(['amount' => 'required', 'client_id' => 'required']);
        $amount = (float) $data['amount'];
        if ($amount <= 0) {
            $this->json(['success' => false, 'message' => 'Payment amount must be greater than zero.'], 422);
        }
        $oldInvoiceId = $payment['invoice_id'] ? (int) $payment['invoice_id'] : null;
        $newInvoiceId = $this->input('invoice_id') ? (int) $this->input('invoice_id') : null;
        $clientId = (int) $data['client_id'];
        $projectId = $this->input('project_id') ? (int) $this->input('project_id') : null;
        if ($newInvoiceId) {
            $invoice = $this->validatePaymentInvoice(
                $newInvoiceId,
                $clientId,
                $amount,
                $newInvoiceId === $oldInvoiceId ? (float) $payment['amount'] : 0
            );
            $projectId = $invoice['project_id'] ? (int) $invoice['project_id'] : $projectId;
        }
        $this->validatePaymentProject($projectId, $clientId);
        Database::beginTransaction();
        try {
            Database::update('payments', [
                'invoice_id' => $newInvoiceId,
                'client_id' => $clientId,
                'project_id' => $projectId,
                'amount' => $amount,
                'payment_stage' => $this->input('payment_stage', 'other'),
                'payment_method' => $this->input('payment_method', 'upi'),
                'transaction_id' => $this->input('transaction_id'),
                'payment_date' => $this->input('payment_date', date('Y-m-d')),
                'notes' => $this->input('notes'),
            ], 'id = ?', [$paymentId]);
            if ($oldInvoiceId) {
                Payment::reconcileInvoice($oldInvoiceId);
            }
            if ($newInvoiceId && $newInvoiceId !== $oldInvoiceId) {
                Payment::reconcileInvoice($newInvoiceId);
            }
            Client::recalculateBalance((int) $payment['client_id']);
            Client::recalculateBalance($clientId);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
        ActivityLog::write('update', 'payment', $paymentId, 'Payment updated');
        $this->json(['success' => true, 'message' => 'Payment updated.']);
    }

    public function deletePayment(string $id): void
    {
        $paymentId = (int) $id;
        $payment = Database::fetch('SELECT * FROM payments WHERE id = ?', [$paymentId]);
        if (!$payment) {
            $this->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }
        Database::delete('payments', 'id = ?', [$paymentId]);
        if ($payment['invoice_id']) {
            Payment::reconcileInvoice((int) $payment['invoice_id']);
        }
        Client::recalculateBalance((int) $payment['client_id']);
        ActivityLog::write('delete', 'payment', $paymentId, 'Payment deleted');
        $this->json(['success' => true, 'message' => 'Payment deleted.']);
    }

    // Invoices
    public function storeInvoice(): void
    {
        $clientId = (int) $this->input('client_id');
        if (!$clientId) {
            $this->json(['success' => false, 'message' => 'Client is required.'], 422);
        }
        $items = json_decode((string) $this->input('items', '[]'), true) ?: [];
        if (empty($items) && !empty($_POST['item_service'])) {
            foreach ($_POST['item_service'] as $i => $service) {
                if (trim((string) $service) === '') {
                    continue;
                }
                $items[] = [
                    'service_name' => $service,
                    'description' => $_POST['item_desc'][$i] ?? '',
                    'quantity' => (float) ($_POST['item_qty'][$i] ?? 1),
                    'rate' => (float) ($_POST['item_rate'][$i] ?? 0),
                ];
            }
        }
        if (empty($items)) {
            $this->json(['success' => false, 'message' => 'At least one line item is required.'], 422);
        }
        $gstRate = (float) $this->input('gst_rate', 18);
        $discount = (float) $this->input('discount_percent', 0);
        $totals = Invoice::calculateTotals($items, $gstRate, $discount);
        $prefix = Database::fetch('SELECT invoice_prefix FROM company_settings LIMIT 1')['invoice_prefix'] ?? 'INV';
        $number = Sequence::next('invoice', $prefix);
        $id = Invoice::create([
            'invoice_number' => $number,
            'client_id' => $clientId,
            'project_id' => $this->input('project_id') ?: null,
            'invoice_date' => $this->input('invoice_date', date('Y-m-d')),
            'due_date' => $this->input('due_date') ?: date('Y-m-d', strtotime('+7 days')),
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount'],
            'discount_percent' => $discount,
            'gst_rate' => $gstRate,
            'gst_amount' => $totals['gst'],
            'total_amount' => $totals['total'],
            'received_amount' => 0,
            'pending_amount' => $totals['total'],
            'status' => 'sent',
            'billing_address' => $this->input('billing_address') ?: null,
            'created_by' => Auth::id(),
        ], array_map(fn($i) => [
            'service_name' => $i['service_name'] ?? 'Service',
            'description' => $i['description'] ?? '',
            'quantity' => $i['quantity'] ?? 1,
            'rate' => $i['rate'] ?? 0,
            'amount' => $i['amount'] ?? 0,
        ], $totals['items']));
        Client::recalculateBalance($clientId);
        ActivityLog::write('create', 'invoice', $id, 'Invoice generated: ' . $number);
        $this->json(['success' => true, 'id' => $id, 'invoice_number' => $number, 'message' => 'Invoice generated.']);
    }

    public function updateInvoice(string $id): void
    {
        $invoiceId = (int) $id;
        $invoice = Database::fetch('SELECT * FROM invoices WHERE id = ?', [$invoiceId]);
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }
        $status = (string) $this->input('status', $invoice['status']);
        Database::beginTransaction();
        try {
            Database::update('invoices', [
                'invoice_date' => $this->input('invoice_date', $invoice['invoice_date']),
                'due_date' => $this->input('due_date') ?: null,
                'status' => $status,
                'billing_address' => $this->input('billing_address') ?: null,
                'notes' => $this->input('notes'),
            ], 'id = ?', [$invoiceId]);

            if ($status === 'paid') {
                $this->recordInvoicePaidAdjustment($invoice);
            }

            if ($status !== 'cancelled') {
                Payment::reconcileInvoice($invoiceId);
            } else {
                Client::recalculateBalance((int) $invoice['client_id']);
            }

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
        ActivityLog::write('update', 'invoice', $invoiceId, 'Invoice updated');
        $this->json(['success' => true, 'message' => 'Invoice updated.']);
    }

    public function deleteInvoice(string $id): void
    {
        $invoiceId = (int) $id;
        $invoice = Database::fetch('SELECT client_id FROM invoices WHERE id = ?', [$invoiceId]);
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }
        Database::delete('invoices', 'id = ?', [$invoiceId]);
        Client::recalculateBalance((int) $invoice['client_id']);
        ActivityLog::write('delete', 'invoice', $invoiceId, 'Invoice deleted');
        $this->json(['success' => true, 'message' => 'Invoice deleted.']);
    }

    // Quotations
    public function storeQuotation(): void
    {
        $clientId = (int) $this->input('client_id');
        if (!$clientId) {
            $this->json(['success' => false, 'message' => 'Client is required.'], 422);
        }
        $items = json_decode((string) $this->input('items', '[]'), true) ?: [];
        if (empty($items)) {
            $items = [['service_name' => $this->input('subject', 'Services'), 'quantity' => 1, 'rate' => (float) $this->input('amount', 0)]];
        }
        $gstRate = (float) $this->input('gst_rate', 18);
        $discount = (float) $this->input('discount_percent', 0);
        $subtotal = 0;
        foreach ($items as &$item) {
            $item['amount'] = ((float) ($item['quantity'] ?? 1)) * ((float) ($item['rate'] ?? 0));
            $subtotal += $item['amount'];
        }
        unset($item);
        $discountAmt = $subtotal * ($discount / 100);
        $taxable = $subtotal - $discountAmt;
        $gst = $taxable * ($gstRate / 100);
        $total = $taxable + $gst;
        $prefix = Database::fetch('SELECT quotation_prefix FROM company_settings LIMIT 1')['quotation_prefix'] ?? 'QUO';
        $number = Sequence::next('quotation', $prefix);
        $id = Quotation::create([
            'quote_number' => $number,
            'client_id' => $clientId,
            'subject' => $this->input('subject'),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmt,
            'discount_percent' => $discount,
            'gst_rate' => $gstRate,
            'gst_amount' => $gst,
            'total_amount' => $total,
            'terms' => $this->input('terms'),
            'valid_until' => $this->input('valid_until') ?: null,
            'status' => $this->input('status', 'draft'),
            'created_by' => Auth::id(),
        ], array_map(fn($i) => [
            'service_name' => $i['service_name'] ?? 'Service',
            'description' => $i['description'] ?? '',
            'quantity' => $i['quantity'] ?? 1,
            'rate' => $i['rate'] ?? 0,
            'amount' => $i['amount'] ?? 0,
        ], $items));
        $this->json(['success' => true, 'id' => $id, 'quote_number' => $number, 'message' => 'Quotation saved.']);
    }

    public function convertQuotation(string $id): void
    {
        $quote = Quotation::find((int) $id);
        if (!$quote) {
            $this->json(['success' => false, 'message' => 'Quotation not found.'], 404);
        }
        $prefix = Database::fetch('SELECT project_prefix FROM company_settings LIMIT 1')['project_prefix'] ?? 'PRJ';
        $code = Sequence::next('project', $prefix);
        $projectId = Project::create([
            'project_code' => $code,
            'client_id' => (int) $quote['client_id'],
            'name' => $quote['subject'] ?: 'Project from ' . $quote['quote_number'],
            'category' => 'branding',
            'selling_price' => $quote['total_amount'],
            'status' => 'quotation_sent',
            'quotation_id' => (int) $id,
            'created_by' => Auth::id(),
        ]);
        Database::update('quotations', ['status' => 'converted'], 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'project_id' => $projectId, 'project_code' => $code, 'message' => 'Converted to project.']);
    }

    public function updateQuotation(string $id): void
    {
        $quotationId = (int) $id;
        if (!Quotation::find($quotationId)) {
            $this->json(['success' => false, 'message' => 'Quotation not found.'], 404);
        }
        Database::update('quotations', [
            'subject' => $this->input('subject'),
            'valid_until' => $this->input('valid_until') ?: null,
            'status' => $this->input('status', 'draft'),
            'terms' => $this->input('terms'),
            'notes' => $this->input('notes'),
        ], 'id = ?', [$quotationId]);
        ActivityLog::write('update', 'quotation', $quotationId, 'Quotation updated');
        $this->json(['success' => true, 'message' => 'Quotation updated.']);
    }

    public function deleteQuotation(string $id): void
    {
        $quotationId = (int) $id;
        $linked = Database::fetch('SELECT COUNT(*) AS total FROM projects WHERE quotation_id = ?', [$quotationId]);
        if ((int) ($linked['total'] ?? 0) > 0) {
            $this->json(['success' => false, 'message' => 'This quotation is linked to a project and cannot be deleted.'], 409);
        }
        Database::delete('quotations', 'id = ?', [$quotationId]);
        ActivityLog::write('delete', 'quotation', $quotationId, 'Quotation deleted');
        $this->json(['success' => true, 'message' => 'Quotation deleted.']);
    }

    // Expenses
    public function storeExpense(): void
    {
        $data = $this->validate(['description' => 'required', 'amount' => 'required']);
        $id = Expense::create([
            'category' => $this->input('category', 'miscellaneous'),
            'description' => $data['description'],
            'amount' => (float) $data['amount'],
            'expense_date' => $this->input('expense_date', date('Y-m-d')),
            'paid_via' => $this->input('paid_via', 'bank_transfer'),
            'created_by' => Auth::id(),
        ]);
        if (!empty($_FILES['receipt'])) {
            try {
                $fileId = FileModel::storeUpload($_FILES['receipt'], null, null);
                $file = FileModel::find($fileId);
                if ($file) {
                    Database::update('expenses', ['receipt_path' => $file['file_path']], 'id = ?', [$id]);
                }
            } catch (\Throwable) {
                // receipt optional
            }
        }
        $this->json(['success' => true, 'id' => $id, 'message' => 'Expense recorded.']);
    }

    public function updateExpense(string $id): void
    {
        $data = $this->validate(['description' => 'required', 'amount' => 'required']);
        Database::update('expenses', [
            'category' => $this->input('category', 'miscellaneous'),
            'description' => $data['description'],
            'amount' => (float) $data['amount'],
            'expense_date' => $this->input('expense_date', date('Y-m-d')),
            'paid_via' => $this->input('paid_via', 'bank_transfer'),
        ], 'id = ?', [(int) $id]);
        ActivityLog::write('update', 'expense', (int) $id, 'Expense updated');
        $this->json(['success' => true, 'message' => 'Expense updated.']);
    }

    public function deleteExpense(string $id): void
    {
        Database::delete('expenses', 'id = ?', [(int) $id]);
        ActivityLog::write('delete', 'expense', (int) $id, 'Expense deleted');
        $this->json(['success' => true, 'message' => 'Expense deleted.']);
    }

    // Employees
    public function storeEmployee(): void
    {
        $data = $this->validate(['name' => 'required']);
        $skills = $this->input('skills');
        $id = Employee::create([
            'name' => $data['name'],
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'designation' => $this->input('designation'),
            'department' => $this->input('department'),
            'skills' => $skills ? json_encode(explode(',', $skills)) : null,
            'salary' => $this->input('salary') ?: null,
            'join_date' => $this->input('join_date') ?: null,
            'status' => 'active',
        ]);
        $this->json(['success' => true, 'id' => $id, 'message' => 'Employee added.']);
    }

    public function updateEmployee(string $id): void
    {
        $data = $this->validate(['name' => 'required']);
        $skills = trim((string) $this->input('skills'));
        Database::update('employees', [
            'name' => $data['name'],
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'designation' => $this->input('designation'),
            'department' => $this->input('department'),
            'skills' => $skills !== '' ? json_encode(array_map('trim', explode(',', $skills))) : null,
            'salary' => $this->input('salary') ?: null,
            'join_date' => $this->input('join_date') ?: null,
            'status' => $this->input('status', 'active'),
        ], 'id = ?', [(int) $id]);
        ActivityLog::write('update', 'employee', (int) $id, 'Employee updated');
        $this->json(['success' => true, 'message' => 'Employee updated.']);
    }

    public function deleteEmployee(string $id): void
    {
        Database::delete('employees', 'id = ?', [(int) $id]);
        ActivityLog::write('delete', 'employee', (int) $id, 'Employee deleted');
        $this->json(['success' => true, 'message' => 'Employee deleted.']);
    }

    public function storeCalendarEvent(): void
    {
        $data = $this->validate(['title' => 'required', 'event_date' => 'required']);
        $id = Database::insert('calendar_events', [
            'title' => $data['title'],
            'event_type' => $this->input('event_type', 'other'),
            'event_date' => $data['event_date'],
            'start_time' => $this->input('start_time') ?: null,
            'end_time' => $this->input('end_time') ?: null,
            'color' => $this->input('color', 'blue'),
            'description' => $this->input('description'),
            'created_by' => Auth::id(),
        ]);
        ActivityLog::write('create', 'calendar_event', $id, 'Calendar event created');
        $this->json(['success' => true, 'id' => $id, 'message' => 'Calendar event created.']);
    }

    public function updateCalendarEvent(string $id): void
    {
        $data = $this->validate(['title' => 'required', 'event_date' => 'required']);
        Database::update('calendar_events', [
            'title' => $data['title'],
            'event_type' => $this->input('event_type', 'other'),
            'event_date' => $data['event_date'],
            'start_time' => $this->input('start_time') ?: null,
            'end_time' => $this->input('end_time') ?: null,
            'color' => $this->input('color', 'blue'),
            'description' => $this->input('description'),
        ], 'id = ?', [(int) $id]);
        ActivityLog::write('update', 'calendar_event', (int) $id, 'Calendar event updated');
        $this->json(['success' => true, 'message' => 'Calendar event updated.']);
    }

    public function deleteCalendarEvent(string $id): void
    {
        Database::delete('calendar_events', 'id = ?', [(int) $id]);
        ActivityLog::write('delete', 'calendar_event', (int) $id, 'Calendar event deleted');
        $this->json(['success' => true, 'message' => 'Calendar event deleted.']);
    }

    // Settings
    public function updateSettings(): void
    {
        $settings = Database::fetch('SELECT id, logo_path, signature_path FROM company_settings LIMIT 1');

        $logoPath = $this->input('logo_path');
        if (!empty($_FILES['logo_file']['tmp_name'])) {
            $logoPath = $this->storeCompanyImage($_FILES['logo_file'], 'logo') ?: $logoPath;
        }

        $signaturePath = $this->input('signature_path');
        if (!empty($_FILES['signature_file']['tmp_name'])) {
            $signaturePath = $this->storeCompanyImage($_FILES['signature_file'], 'signature') ?: $signaturePath;
        }

        $data = array_filter([
            'company_name' => $this->input('company_name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'address' => $this->input('address'),
            'gst_number' => $this->input('gst_number'),
            'website' => $this->input('website'),
            'logo_path' => $logoPath,
            'signature_path' => $signaturePath,
            'bank_name' => $this->input('bank_name'),
            'bank_account' => $this->input('bank_account'),
            'bank_ifsc' => $this->input('bank_ifsc'),
            'upi_id' => $this->input('upi_id'),
            'financial_year_start' => $this->input('financial_year_start'),
            'invoice_terms' => $this->input('invoice_terms'),
            'quotation_terms' => $this->input('quotation_terms'),
            'smtp_host' => $this->input('smtp_host'),
            'smtp_port' => $this->input('smtp_port'),
            'smtp_user' => $this->input('smtp_user'),
            'smtp_encryption' => $this->input('smtp_encryption'),
            'notify_payment_overdue' => $this->input('notify_payment_overdue') !== null ? (int) $this->input('notify_payment_overdue') : null,
            'notify_deadline' => $this->input('notify_deadline') !== null ? (int) $this->input('notify_deadline') : null,
            'notify_task_assigned' => $this->input('notify_task_assigned') !== null ? (int) $this->input('notify_task_assigned') : null,
        ], fn($v) => $v !== null);
        if ($this->input('smtp_pass') !== null && $this->input('smtp_pass') !== '') {
            $data['smtp_pass'] = $this->input('smtp_pass');
        }
        if ($settings) {
            Database::update('company_settings', $data, 'id = ?', [$settings['id']]);
        } else {
            Database::insert('company_settings', $data);
        }
        $this->json(['success' => true, 'message' => 'Settings saved.']);
    }

    private function storeCompanyImage(array $file, string $prefix): string
    {
        $uploadDir = PUBLIC_PATH . '/uploads/company/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $ext = 'png';
        }

        $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file((string) $file['tmp_name'], $uploadDir . $filename)) {
            return 'uploads/company/' . $filename;
        }

        return '';
    }

    public function updateUser(string $id): void
    {
        if (!Auth::hasRole('admin')) {
            $this->json(['success' => false, 'message' => 'Only administrators can manage users.'], 403);
        }
        $data = $this->validate(['name' => 'required', 'email' => 'required|email']);
        Database::update('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $this->input('role', 'designer'),
            'is_active' => (int) $this->input('is_active', 1),
        ], 'id = ?', [(int) $id]);
        ActivityLog::write('update', 'user', (int) $id, 'User updated');
        $this->json(['success' => true, 'message' => 'User updated.']);
    }

    public function deleteUser(string $id): void
    {
        $userId = (int) $id;
        if (!Auth::hasRole('admin')) {
            $this->json(['success' => false, 'message' => 'Only administrators can manage users.'], 403);
        }
        if ($userId === Auth::id()) {
            $this->json(['success' => false, 'message' => 'You cannot delete your own account.'], 409);
        }
        $user = Database::fetch('SELECT role FROM users WHERE id = ?', [$userId]);
        if (($user['role'] ?? '') === 'admin') {
            $admins = Database::fetch("SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND is_active = 1");
            if ((int) ($admins['total'] ?? 0) <= 1) {
                $this->json(['success' => false, 'message' => 'The last active administrator cannot be deleted.'], 409);
            }
        }
        Database::delete('users', 'id = ?', [$userId]);
        ActivityLog::write('delete', 'user', $userId, 'User deleted');
        $this->json(['success' => true, 'message' => 'User deleted.']);
    }

    // Files
    public function uploadFile(): void
    {
        if (empty($_FILES['file'])) {
            $this->json(['success' => false, 'message' => 'No file uploaded.'], 422);
        }
        try {
            $id = FileModel::storeUpload(
                $_FILES['file'],
                $this->input('project_id') ? (int) $this->input('project_id') : null,
                $this->input('client_id') ? (int) $this->input('client_id') : null
            );
            ActivityLog::write('upload', 'file', $id, 'File uploaded: ' . $_FILES['file']['name']);
            $this->json(['success' => true, 'id' => $id, 'message' => 'File uploaded successfully.']);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function deleteFile(string $id): void
    {
        if (FileModel::delete((int) $id)) {
            ActivityLog::write('delete', 'file', (int) $id, 'File deleted');
            $this->json(['success' => true, 'message' => 'File deleted.']);
        }
        $this->json(['success' => false, 'message' => 'File not found.'], 404);
    }

    public function updateFile(string $id): void
    {
        $fileId = (int) $id;
        if (!FileModel::find($fileId)) {
            $this->json(['success' => false, 'message' => 'File not found.'], 404);
        }
        $data = $this->validate(['original_name' => 'required']);
        Database::update('files', [
            'original_name' => $data['original_name'],
            'project_id' => $this->input('project_id') ?: null,
            'client_id' => $this->input('client_id') ?: null,
        ], 'id = ?', [$fileId]);
        ActivityLog::write('update', 'file', $fileId, 'File details updated');
        $this->json(['success' => true, 'message' => 'File details updated.']);
    }

    // Communications
    public function storeCommunication(): void
    {
        $clientId = (int) $this->input('client_id');
        $message = trim((string) $this->input('message'));
        if (!$clientId || $message === '') {
            $this->json(['success' => false, 'message' => 'Client and message are required.'], 422);
        }
        $id = Communication::create([
            'client_id' => $clientId,
            'type' => $this->input('type', 'note'),
            'subject' => $this->input('subject'),
            'message' => $message,
        ]);
        $this->json(['success' => true, 'id' => $id, 'message' => 'Communication logged.']);
    }

    // Backup
    public function createBackup(): void
    {
        if (!Auth::hasRole(['admin', 'manager'])) {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $result = BackupService::create();
        $this->json(['success' => true, 'message' => 'Backup created.', 'backup' => $result]);
    }

    public function generateBackupToken(): void
    {
        if (!Auth::hasRole('admin')) {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $token = BackupService::generateToken();
        $base = preg_replace('#/public/?$#', '', config('app.url'));
        $this->json(['success' => true, 'token' => $token, 'cron_url' => $base . '/backup.php?token=' . $token]);
    }

    // Dashboard stats (AJAX refresh)
    public function dashboardStats(): void
    {
        $this->json(['success' => true, 'data' => Dashboard::stats()]);
    }

    private function recalculateInvoice(int $invoiceId): void
    {
        $invoice = Database::fetch('SELECT * FROM invoices WHERE id = ?', [$invoiceId]);
        if (!$invoice) {
            return;
        }
        $row = Database::fetch('SELECT COALESCE(SUM(amount), 0) AS received FROM payments WHERE invoice_id = ?', [$invoiceId]);
        $received = (float) ($row['received'] ?? 0);
        $pending = max(0, (float) $invoice['total_amount'] - $received);
        $status = $pending <= 0 ? 'paid' : ($received > 0 ? 'partial' : 'sent');
        if ($pending > 0 && $invoice['due_date'] && $invoice['due_date'] < date('Y-m-d')) {
            $status = 'overdue';
        }
        Database::update('invoices', [
            'received_amount' => $received,
            'pending_amount' => $pending,
            'status' => $status,
        ], 'id = ?', [$invoiceId]);
        Client::recalculateBalance((int) $invoice['client_id']);
    }

    private function recordInvoicePaidAdjustment(array $invoice): void
    {
        $sum = Database::fetch(
            'SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE invoice_id = ?',
            [(int) $invoice['id']]
        );
        $missingAmount = round((float) $invoice['total_amount'] - (float) ($sum['total'] ?? 0), 2);
        if ($missingAmount <= 0) {
            return;
        }

        Payment::create([
            'invoice_id' => (int) $invoice['id'],
            'client_id' => (int) $invoice['client_id'],
            'project_id' => $invoice['project_id'] ? (int) $invoice['project_id'] : null,
            'amount' => $missingAmount,
            'payment_stage' => 'final',
            'payment_method' => 'other',
            'transaction_id' => 'Marked paid',
            'payment_date' => date('Y-m-d'),
            'gst_included' => 1,
            'notes' => 'Auto-created when invoice status was marked paid.',
            'created_by' => Auth::id(),
        ]);
    }

    private function validatePaymentInvoice(int $invoiceId, int $clientId, float $amount, float $existingAmount = 0): array
    {
        $invoice = Database::fetch('SELECT * FROM invoices WHERE id = ?', [$invoiceId]);
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Selected invoice was not found.'], 404);
        }
        if ((int) $invoice['client_id'] !== $clientId) {
            $this->json(['success' => false, 'message' => 'Selected invoice does not belong to this client.'], 422);
        }
        if ($invoice['status'] === 'cancelled') {
            $this->json(['success' => false, 'message' => 'Payments cannot be applied to a cancelled invoice.'], 422);
        }
        $available = (float) $invoice['pending_amount'] + $existingAmount;
        if ($amount > $available + 0.009) {
            $this->json([
                'success' => false,
                'message' => 'Payment exceeds the invoice balance of ' . format_money($available) . '.',
            ], 422);
        }
        return $invoice;
    }

    private function validatePaymentProject(?int $projectId, int $clientId): void
    {
        if (!$projectId) {
            return;
        }
        $project = Database::fetch('SELECT client_id FROM projects WHERE id = ?', [$projectId]);
        if (!$project) {
            $this->json(['success' => false, 'message' => 'Selected project was not found.'], 404);
        }
        if ((int) $project['client_id'] !== $clientId) {
            $this->json(['success' => false, 'message' => 'Selected project does not belong to this client.'], 422);
        }
    }
}
