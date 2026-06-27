<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Payment
{
    public static function all(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['client_id'])) {
            $where[] = 'py.client_id = ?';
            $params[] = $filters['client_id'];
        }
        if (!empty($filters['invoice_id'])) {
            $where[] = 'py.invoice_id = ?';
            $params[] = $filters['invoice_id'];
        }
        if (!empty($filters['project_id'])) {
            $where[] = 'py.project_id = ?';
            $params[] = $filters['project_id'];
        }
        if (!empty($filters['method'])) {
            $where[] = 'py.payment_method = ?';
            $params[] = $filters['method'];
        }
        if (!empty($filters['stage'])) {
            $where[] = 'py.payment_stage = ?';
            $params[] = $filters['stage'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'py.payment_date >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'py.payment_date <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(c.company_name LIKE ? OR i.invoice_number LIKE ? OR p.name LIKE ? OR py.transaction_id LIKE ?)';
            $query = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$query, $query, $query, $query]);
        }
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT py.*, c.company_name as client_name, i.invoice_number, p.name as project_name
                FROM payments py
                JOIN clients c ON c.id = py.client_id
                LEFT JOIN invoices i ON i.id = py.invoice_id
                LEFT JOIN projects p ON p.id = py.project_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY py.payment_date DESC LIMIT {$perPage} OFFSET {$offset}";
        return Database::fetchAll($sql, $params);
    }


    public static function create(array $data): int
    {
        return Database::insert('payments', $data);
    }

    public static function recent(int $limit = 5, array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['month'])) {
            $dateStart = $filters['month'] . '-01';
            $dateEnd = date('Y-m-t', strtotime($dateStart));
            $where[] = 'py.payment_date BETWEEN ? AND ?';
            $params[] = $dateStart;
            $params[] = $dateEnd;
        }
        $whereSql = implode(' AND ', $where);

        return Database::fetchAll(
            "SELECT py.*, c.company_name as client_name, i.invoice_number
             FROM payments py JOIN clients c ON c.id = py.client_id
             LEFT JOIN invoices i ON i.id = py.invoice_id
             WHERE $whereSql
             ORDER BY py.payment_date DESC LIMIT {$limit}",
             $params
        );
    }

    public static function stats(array $filters = []): array
    {
        $monthFilter = $filters['month'] ?? null;
        $monthStart = $monthFilter ? $monthFilter . '-01' : date('Y-m-01');
        $monthEnd = $monthFilter ? date('Y-m-t', strtotime($monthStart)) : date('Y-m-t');

        $revenue = Database::fetch(
            'SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_date BETWEEN ? AND ?',
            [$monthStart, $monthEnd]
        );
        $pending = Database::fetch(
            "SELECT COALESCE(SUM(pending_amount), 0) as total FROM invoices WHERE status NOT IN ('paid','cancelled')"
        );
        $overdue = Database::fetch(
            "SELECT COALESCE(SUM(pending_amount), 0) as total FROM invoices WHERE status = 'overdue' OR (due_date < CURDATE() AND status IN ('sent','partial'))"
        );
        $billed = Database::fetch('SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE status != "cancelled"');
        $received = Database::fetch('SELECT COALESCE(SUM(amount), 0) as total FROM payments');
        $unallocated = Database::fetch('SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE invoice_id IS NULL');
        $monthCount = Database::fetch(
            'SELECT COUNT(*) as total FROM payments WHERE payment_date BETWEEN ? AND ?',
            [$monthStart, $monthEnd]
        );
        return [
            'revenue_month' => (float) ($revenue['total'] ?? 0),
            'pending' => (float) ($pending['total'] ?? 0),
            'overdue' => (float) ($overdue['total'] ?? 0),
            'total_billed' => (float) ($billed['total'] ?? 0),
            'total_received' => (float) ($received['total'] ?? 0),
            'unallocated' => (float) ($unallocated['total'] ?? 0),
            'month_count' => (int) ($monthCount['total'] ?? 0),
            'collection_rate' => (float) ($billed['total'] ?? 0) > 0
                ? round(((float) ($received['total'] ?? 0) / (float) $billed['total']) * 100, 1)
                : 0,
        ];
    }

    public static function reconcileInvoice(int $invoiceId): void
    {
        $invoice = Database::fetch('SELECT * FROM invoices WHERE id = ?', [$invoiceId]);
        if (!$invoice) {
            return;
        }
        $sum = Database::fetch(
            'SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE invoice_id = ?',
            [$invoiceId]
        );
        $received = (float) ($sum['total'] ?? 0);
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

    public static function applyToInvoice(int $invoiceId, float $amount = 0): void
    {
        self::reconcileInvoice($invoiceId);
    }
}
