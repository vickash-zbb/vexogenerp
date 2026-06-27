<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Dashboard;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Project;

class ReportController extends Controller
{
    public function index(): void
    {
        $year = max(2020, min(2100, (int) $this->input('year', date('Y'))));

        // Fetch financial year start month from settings (default to 4, i.e., April)
        $settings = Database::fetch('SELECT financial_year_start FROM company_settings LIMIT 1');
        $fysMonth = (int) ($settings['financial_year_start'] ?? 4);

        // Compute date range for the financial year starting in $year
        $fyStartDate = sprintf('%04d-%02d-01', $year, $fysMonth);
        $fyEndDate = date('Y-m-t', strtotime("$fyStartDate +11 months"));
        $yearLabel = ($fysMonth === 1) ? (string)$year : $year . '-' . substr((string)($year + 1), 2);
        $requestedFrom = (string) $this->input('from_date', '');
        $requestedTo = (string) $this->input('to_date', '');
        $startDate = $this->validDate($requestedFrom) ? $requestedFrom : $fyStartDate;
        $endDate = $this->validDate($requestedTo) ? $requestedTo : $fyEndDate;
        if (strtotime($startDate) > strtotime($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }
        $rangeLabel = format_date($startDate, 'M j, Y') . ' - ' . format_date($endDate, 'M j, Y');

        $ytdRevenue = Database::fetch(
            'SELECT COALESCE(SUM(amount), 0) as t FROM payments WHERE payment_date BETWEEN ? AND ?',
            [$startDate, $endDate]
        );
        $ytdExpenses = Database::fetch(
            'SELECT COALESCE(SUM(amount), 0) as t FROM expenses WHERE expense_date BETWEEN ? AND ?',
            [$startDate, $endDate]
        );
        $rev = (float) ($ytdRevenue['t'] ?? 0);
        $exp = (float) ($ytdExpenses['t'] ?? 0);

        $serviceRevenue = Database::fetchAll(
            "SELECT COALESCE(p.category, 'unassigned') as category, COALESCE(SUM(py.amount), 0) as total
             FROM payments py
             LEFT JOIN projects p ON p.id = py.project_id
             WHERE py.payment_date BETWEEN ? AND ?
             GROUP BY COALESCE(p.category, 'unassigned') ORDER BY total DESC",
            [$startDate, $endDate]
        );

        // Optimize monthly cash performance queries (reducing 24 queries to 2)
        $payments = Database::fetchAll(
            "SELECT DATE_FORMAT(payment_date, '%Y-%m') as ym, SUM(amount) as total 
             FROM payments 
             WHERE payment_date BETWEEN ? AND ? 
             GROUP BY DATE_FORMAT(payment_date, '%Y-%m')",
            [$startDate, $endDate]
        );
        $expenses = Database::fetchAll(
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
        foreach ($expenses as $e) {
            $expMap[$e['ym']] = (float) $e['total'];
        }

        $monthly = ['months' => [], 'revenue' => [], 'expenses' => [], 'profit' => []];
        $rangeStart = new \DateTime(date('Y-m-01', strtotime($startDate)));
        $rangeEnd = new \DateTime(date('Y-m-01', strtotime($endDate)));
        while ($rangeStart <= $rangeEnd) {
            $currentMonthDate = $rangeStart->format('Y-m');
            $monthly['months'][] = $rangeStart->format('M Y');

            $monthRevenue = $payMap[$currentMonthDate] ?? 0.0;
            $monthExpense = $expMap[$currentMonthDate] ?? 0.0;

            $monthly['revenue'][] = round($monthRevenue / 1000, 1);
            $monthly['expenses'][] = round($monthExpense / 1000, 1);
            $monthly['profit'][] = round(($monthRevenue - $monthExpense) / 1000, 1);
            $rangeStart->modify('+1 month');
        }

        $completedProjects = Database::fetch(
            "SELECT COUNT(*) as cnt FROM projects
             WHERE status IN ('completed','delivered','closed') AND updated_at BETWEEN ? AND ?",
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );

        $receivables = Database::fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN due_date >= CURDATE() THEN pending_amount ELSE 0 END), 0) as current_due,
                COALESCE(SUM(CASE WHEN due_date < CURDATE() AND due_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN pending_amount ELSE 0 END), 0) as overdue_30,
                COALESCE(SUM(CASE WHEN due_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND due_date >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) THEN pending_amount ELSE 0 END), 0) as overdue_60,
                COALESCE(SUM(CASE WHEN due_date < DATE_SUB(CURDATE(), INTERVAL 60 DAY) THEN pending_amount ELSE 0 END), 0) as overdue_60_plus
             FROM invoices WHERE status NOT IN ('paid','cancelled')"
        ) ?: [];

        $topClients = Database::fetchAll(
            "SELECT c.company_name, COALESCE(SUM(py.amount), 0) as revenue, COUNT(py.id) as payments
             FROM payments py JOIN clients c ON c.id = py.client_id
             WHERE py.payment_date BETWEEN ? AND ?
             GROUP BY c.id, c.company_name ORDER BY revenue DESC LIMIT 10",
            [$startDate, $endDate]
        );

        $paymentMethods = Database::fetchAll(
            "SELECT payment_method, COALESCE(SUM(amount), 0) as total, COUNT(*) as transactions
             FROM payments WHERE payment_date BETWEEN ? AND ?
             GROUP BY payment_method ORDER BY total DESC",
            [$startDate, $endDate]
        );

        $expenseCategories = Database::fetchAll(
            "SELECT category, COALESCE(SUM(amount), 0) as total
             FROM expenses WHERE expense_date BETWEEN ? AND ?
             GROUP BY category ORDER BY total DESC",
            [$startDate, $endDate]
        );

        $invoiceSummary = Database::fetch(
            "SELECT
                COALESCE(SUM(total_amount), 0) as billed,
                COALESCE(SUM(received_amount), 0) as received,
                COALESCE(SUM(pending_amount), 0) as pending,
                COUNT(*) as invoices
             FROM invoices WHERE invoice_date BETWEEN ? AND ? AND status != 'cancelled'",
            [$startDate, $endDate]
        ) ?: [];

        $this->view('reports/index', [
            'title' => 'Reports',
            'page' => 'reports',
            'chartData' => $monthly,
            'ytdRevenue' => $rev,
            'ytdExpenses' => $exp,
            'ytdProfit' => $rev - $exp,
            'completedProjects' => (int) ($completedProjects['cnt'] ?? 0),
            'serviceRevenue' => $serviceRevenue,
            'year' => $year,
            'yearLabel' => $yearLabel,
            'rangeLabel' => $rangeLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'fysMonth' => $fysMonth,
            'receivables' => $receivables,
            'topClients' => $topClients,
            'paymentMethods' => $paymentMethods,
            'expenseCategories' => $expenseCategories,
            'invoiceSummary' => $invoiceSummary,
            'profitMargin' => $rev > 0 ? round((($rev - $exp) / $rev) * 100, 1) : 0,
        ]);
    }

    private function validDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);
        return $parsed instanceof \DateTime && $parsed->format('Y-m-d') === $date;
    }
}
