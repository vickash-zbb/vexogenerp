<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

class Dashboard
{
    public static function stats(array $filters = []): array
    {
        $clients = Database::fetch('SELECT COUNT(*) as cnt FROM clients');
        $projects = Project::stats($filters);
        $payments = Payment::stats($filters);
        
        $monthFilter = $filters['month'] ?? null;
        $expenses = Expense::monthTotal($monthFilter);
        $revenue = $payments['revenue_month'];
        $profit = $revenue - $expenses;
        $cash = Database::fetch(
            'SELECT
                (SELECT COALESCE(SUM(amount), 0) FROM payments) -
                (SELECT COALESCE(SUM(amount), 0) FROM expenses) as total'
        );
        
        $dateStart = $monthFilter ? $monthFilter . '-01' : date('Y-m-01');
        $dateEnd = $monthFilter ? date('Y-m-t', strtotime($dateStart)) : date('Y-m-t');
        
        $billedMonth = Database::fetch(
            'SELECT COALESCE(SUM(total_amount), 0) as total
             FROM invoices WHERE invoice_date BETWEEN ? AND ? AND status != "cancelled"',
            [$dateStart, $dateEnd]
        );
        $leads = Database::fetch("SELECT COUNT(*) as total FROM projects WHERE status = 'lead'");
        $converted = Database::fetch("SELECT COUNT(*) as total FROM projects WHERE status NOT IN ('lead','discussion')");
        $leadTotal = (int) ($leads['total'] ?? 0);
        $convTotal = (int) ($converted['total'] ?? 0);
        $conversion = $leadTotal + $convTotal > 0 ? round(($convTotal / ($leadTotal + $convTotal)) * 100) : 0;

        return [
            'total_clients' => (int) ($clients['cnt'] ?? 0),
            'active_projects' => $projects['active'],
            'completed_projects' => $projects['completed'],
            'pending_projects' => $projects['pending'],
            'pending_payments' => $payments['pending'],
            'revenue_month' => $revenue,
            'expenses_month' => $expenses,
            'net_profit' => $profit,
            'cash_in_hand' => (float) ($cash['total'] ?? 0),
            'billed_month' => (float) ($billedMonth['total'] ?? 0),
            'collection_rate' => $payments['collection_rate'],
            'overdue_payments' => $payments['overdue'],
            'upcoming_deliveries' => $projects['upcoming_deliveries'],
            'lead_conversion' => $conversion,
            'margin' => $revenue > 0 ? round(($profit / $revenue) * 100) : 0,
        ];
    }

    public static function chartData(): array
    {
        $months = [];
        $revenue = [];
        $expenses = [];

        // Use the first of current month to avoid day-rollover bugs (e.g., on the 31st of a month)
        $baseDate = date('Y-m-01');
        $startDate = date('Y-m-01', strtotime("$baseDate -11 months"));
        $endDate = date('Y-m-t'); // end of the current month

        $payments = Database::fetchAll(
            "SELECT DATE_FORMAT(payment_date, '%Y-%m') as ym, SUM(amount) as total 
             FROM payments 
             WHERE payment_date BETWEEN ? AND ? 
             GROUP BY DATE_FORMAT(payment_date, '%Y-%m')",
            [$startDate, $endDate]
        );
        $expensesData = Database::fetchAll(
            "SELECT DATE_FORMAT(expense_date, '%Y-%m') as ym, SUM(amount) as total 
             FROM expenses 
             WHERE expense_date BETWEEN ? AND ? 
             GROUP BY DATE_FORMAT(expense_date, '%Y-%m')",
            [$startDate, $endDate]
        );

        $payMap = [];
        foreach ($payments as $p) {
            $payMap[$p['ym']] = (float) $p['total'];
        }
        $expMap = [];
        foreach ($expensesData as $e) {
            $expMap[$e['ym']] = (float) $e['total'];
        }

        for ($i = 11; $i >= 0; $i--) {
            $currentMonthDate = date('Y-m', strtotime("$baseDate -{$i} months"));
            $monthName = date('M', strtotime("$baseDate -{$i} months"));

            $months[] = $monthName;
            $revenue[] = round(($payMap[$currentMonthDate] ?? 0.0) / 1000, 1);
            $expenses[] = round(($expMap[$currentMonthDate] ?? 0.0) / 1000, 1);
        }

        return compact('months', 'revenue', 'expenses');
    }

    public static function projectStatusChart(array $filters = []): array
    {
        $statuses = Project::byStatus($filters);
        $active = 0;
        $completed = 0;
        $review = 0;
        $lead = 0;
        foreach ($statuses as $status => $cnt) {
            if (in_array($status, ['completed', 'delivered', 'closed'], true)) {
                $completed += $cnt;
            } elseif (in_array($status, ['review', 'revision', 'final_approval'], true)) {
                $review += $cnt;
            } elseif (in_array($status, ['lead', 'discussion', 'quotation_sent'], true)) {
                $lead += $cnt;
            } else {
                $active += $cnt;
            }
        }
        return ['active' => $active, 'completed' => $completed, 'review' => $review, 'lead' => $lead];
    }

    public static function notifications(int $limit = 10): array
    {
        $userId = Auth::id();
        if (!$userId) {
            return [];
        }
        return Database::fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?',
            [$userId, $limit]
        );
    }

    public static function unreadCount(): int
    {
        $userId = Auth::id();
        if (!$userId) {
            return 0;
        }
        $row = Database::fetch('SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);
        return (int) ($row['cnt'] ?? 0);
    }
}
