<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index(): void
    {
        $clientFilters = [
            'status' => $this->input('client_status'),
            'search' => $this->input('client_q'),
        ];
        $filters = [
            'client_id' => null,
            'status' => $this->input('status'),
            'category' => $this->input('service'),
            'assigned_to' => $this->input('employee_id'),
            'priority' => $this->input('priority'),
            'balance' => $this->input('balance'),
            'delivery' => $this->input('delivery'),
            'search' => $this->input('project_q'),
        ];

        $clientRows = $this->clientRows(array_filter($clientFilters));
        $selectedClientId = (int) ($this->input('client_id') ?: ($clientRows[0]['id'] ?? 0));
        $selectedClient = $selectedClientId ? Client::find($selectedClientId) : null;
        $filters['client_id'] = $selectedClientId ?: null;

        $clientProjects = $selectedClientId ? $this->clientProjects($selectedClientId, array_filter($filters)) : [];
        $summary = $selectedClientId ? $this->clientSummary($selectedClientId) : [];
        $payments = $selectedClientId ? $this->clientPayments($selectedClientId) : [];
        $quotations = $selectedClientId ? $this->clientQuotations($selectedClientId) : [];
        $invoices = $selectedClientId ? $this->clientInvoices($selectedClientId) : [];
        $files = $selectedClientId ? $this->clientFiles($selectedClientId) : [];
        $notes = $selectedClientId ? $this->clientNotes($selectedClientId) : [];
        $timeline = $selectedClientId ? $this->clientTimeline($selectedClientId) : [];
        $lastPayment = $payments[0] ?? null;
        $nextDue = $selectedClientId ? $this->nextDueDate($selectedClientId) : null;

        $this->view('projects/index', [
            'title' => 'Projects',
            'page' => 'projects',
            'clientRows' => $clientRows,
            'selectedClient' => $selectedClient,
            'selectedClientId' => $selectedClientId,
            'projects' => $clientProjects,
            'summary' => $summary,
            'filters' => $filters,
            'clientFilters' => $clientFilters,
            'clients' => Client::dropdown(),
            'employees' => Employee::dropdown(),
            'payments' => $payments,
            'quotations' => $quotations,
            'invoices' => $invoices,
            'files' => $files,
            'notes' => $notes,
            'timeline' => $timeline,
            'lastPayment' => $lastPayment,
            'nextDue' => $nextDue,
        ]);
    }

    private function clientRows(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'c.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(c.company_name LIKE ? OR c.contact_person LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)';
            $q = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$q, $q, $q, $q]);
        }

        return Database::fetchAll(
            "SELECT c.*,
                (SELECT COUNT(*) FROM projects p WHERE p.client_id = c.id AND p.status NOT IN ('completed','delivered','closed')) as active_projects,
                COALESCE((SELECT SUM(i.pending_amount) FROM invoices i WHERE i.client_id = c.id AND i.status NOT IN ('paid','cancelled')), 0) as pending_balance,
                (SELECT MAX(p.updated_at) FROM projects p WHERE p.client_id = c.id) as last_project_update
             FROM clients c
             WHERE " . implode(' AND ', $where) . "
             ORDER BY COALESCE(last_project_update, c.updated_at) DESC, c.company_name ASC
             LIMIT 80",
            $params
        );
    }

    private function clientProjects(int $clientId, array $filters = []): array
    {
        $where = ['p.client_id = ?'];
        $params = [$clientId];
        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'p.category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['assigned_to'])) {
            $where[] = 'p.assigned_employee_id = ?';
            $params[] = $filters['assigned_to'];
        }
        if (!empty($filters['priority'])) {
            $where[] = 'p.priority = ?';
            $params[] = $filters['priority'];
        }
        if (!empty($filters['delivery'])) {
            if ($filters['delivery'] === 'overdue') {
                $where[] = "p.expected_delivery < CURDATE() AND p.status NOT IN ('completed','delivered','closed')";
            } elseif ($filters['delivery'] === 'week') {
                $where[] = "p.expected_delivery BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            }
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE ? OR p.project_code LIKE ? OR p.description LIKE ?)';
            $q = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$q, $q, $q]);
        }
        if (!empty($filters['balance'])) {
            if ($filters['balance'] === 'pending') {
                $where[] = 'COALESCE(fin.received_amount, 0) < p.selling_price';
            } elseif ($filters['balance'] === 'paid') {
                $where[] = 'p.selling_price > 0 AND COALESCE(fin.received_amount, 0) >= p.selling_price';
            }
        }

        return Database::fetchAll(
            "SELECT p.*, e.name as assigned_name,
                COALESCE(fin.received_amount, 0) as received_amount,
                p.advance as advance_amount,
                GREATEST(p.balance - COALESCE(fin.non_advance_received, 0), 0) as balance_amount
             FROM projects p
             LEFT JOIN employees e ON e.id = p.assigned_employee_id
             LEFT JOIN (
                SELECT project_id,
                    SUM(amount) as received_amount,
                    SUM(CASE WHEN payment_stage != 'advance' THEN amount ELSE 0 END) as non_advance_received
                FROM payments
                WHERE project_id IS NOT NULL
                GROUP BY project_id
             ) fin ON fin.project_id = p.id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY FIELD(p.status,'lead','quotation_sent','advance_received','planning','design','development','review','revision','completed','delivered','closed'),
                p.expected_delivery IS NULL, p.expected_delivery ASC, p.updated_at DESC",
            $params
        );
    }

    private function clientSummary(int $clientId): array
    {
        $project = Database::fetch(
            "SELECT
                COUNT(*) as total_projects,
                SUM(CASE WHEN status IN ('completed','delivered','closed') THEN 1 ELSE 0 END) as completed_projects,
                SUM(CASE WHEN status NOT IN ('completed','delivered','closed') THEN 1 ELSE 0 END) as active_projects,
                COALESCE(SUM(selling_price), 0) as total_business
             FROM projects WHERE client_id = ?",
            [$clientId]
        ) ?: [];
        $finance = Database::fetch(
            "SELECT
                COALESCE(SUM(amount), 0) as received_amount,
                COALESCE(SUM(CASE WHEN payment_stage = 'advance' THEN amount ELSE 0 END), 0) as advance_received
             FROM payments WHERE client_id = ?",
            [$clientId]
        ) ?: [];
        $invoice = Database::fetch(
            "SELECT
                COALESCE(SUM(pending_amount), 0) as pending_balance,
                COALESCE(MIN(CASE WHEN pending_amount > 0 AND due_date >= CURDATE() THEN due_date END), MIN(CASE WHEN pending_amount > 0 THEN due_date END)) as next_due_date
             FROM invoices WHERE client_id = ? AND status NOT IN ('paid','cancelled')",
            [$clientId]
        ) ?: [];

        return array_merge($project, $finance, $invoice);
    }

    private function clientPayments(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT py.*, p.name as project_name, i.invoice_number
             FROM payments py
             LEFT JOIN projects p ON p.id = py.project_id
             LEFT JOIN invoices i ON i.id = py.invoice_id
             WHERE py.client_id = ?
             ORDER BY py.payment_date DESC, py.id DESC
             LIMIT 50",
            [$clientId]
        );
    }

    private function clientQuotations(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT id, quote_number, subject, total_amount, status, created_at, valid_until
             FROM quotations
             WHERE client_id = ?
             ORDER BY created_at DESC
             LIMIT 30",
            [$clientId]
        );
    }

    private function clientInvoices(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT id, invoice_number, invoice_date, due_date, total_amount, received_amount, pending_amount, status
             FROM invoices
             WHERE client_id = ?
             ORDER BY invoice_date DESC, id DESC
             LIMIT 30",
            [$clientId]
        );
    }

    private function clientFiles(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT f.*, p.name as project_name
             FROM files f
             LEFT JOIN projects p ON p.id = f.project_id
             WHERE f.client_id = ? OR p.client_id = ?
             ORDER BY f.created_at DESC
             LIMIT 40",
            [$clientId, $clientId]
        );
    }

    private function clientNotes(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT pc.*, p.name as project_name, u.name as user_name
             FROM project_comments pc
             JOIN projects p ON p.id = pc.project_id
             LEFT JOIN users u ON u.id = pc.user_id
             WHERE p.client_id = ?
             ORDER BY pc.created_at DESC
             LIMIT 30",
            [$clientId]
        );
    }

    private function clientTimeline(int $clientId): array
    {
        $rows = [];
        $rows = array_merge($rows, Database::fetchAll(
            "SELECT 'Project Created' as title, p.name as detail, p.created_at as created_at, 'project' as type
             FROM projects p WHERE p.client_id = ?",
            [$clientId]
        ));
        $rows = array_merge($rows, Database::fetchAll(
            "SELECT 'Payment Recorded' as title, CONCAT(p.payment_stage, ' - ', p.amount) as detail, p.created_at as created_at, 'payment' as type
             FROM payments p WHERE p.client_id = ?",
            [$clientId]
        ));
        $rows = array_merge($rows, Database::fetchAll(
            "SELECT 'Quotation Sent' as title, q.quote_number as detail, q.created_at as created_at, 'quotation' as type
             FROM quotations q WHERE q.client_id = ?",
            [$clientId]
        ));
        $rows = array_merge($rows, Database::fetchAll(
            "SELECT 'Invoice Generated' as title, i.invoice_number as detail, i.created_at as created_at, 'invoice' as type
             FROM invoices i WHERE i.client_id = ?",
            [$clientId]
        ));
        $rows = array_merge($rows, Database::fetchAll(
            "SELECT 'File Uploaded' as title, f.original_name as detail, f.created_at as created_at, 'file' as type
             FROM files f
             LEFT JOIN projects p ON p.id = f.project_id
             WHERE f.client_id = ? OR p.client_id = ?",
            [$clientId, $clientId]
        ));

        usort($rows, fn($a, $b) => strcmp((string) $b['created_at'], (string) $a['created_at']));
        return array_slice($rows, 0, 40);
    }

    private function nextDueDate(int $clientId): ?array
    {
        return Database::fetch(
            "SELECT invoice_number, due_date, pending_amount
             FROM invoices
             WHERE client_id = ? AND pending_amount > 0 AND status NOT IN ('paid','cancelled')
             ORDER BY due_date IS NULL, due_date ASC
             LIMIT 1",
            [$clientId]
        );
    }
}
