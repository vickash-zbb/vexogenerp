<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Expense
{
    public static function all(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['month'])) {
            $where[] = 'MONTH(expense_date) = ? AND YEAR(expense_date) = ?';
            $parts = explode('-', $filters['month']);
            $params[] = (int) ($parts[1] ?? date('m'));
            $params[] = (int) ($parts[0] ?? date('Y'));
        }
        if (!empty($filters['category'])) {
            $where[] = 'category = ?';
            $params[] = $filters['category'];
        }
        return Database::fetchAll(
            'SELECT * FROM expenses WHERE ' . implode(' AND ', $where) . ' ORDER BY expense_date DESC',
            $params
        );
    }

    public static function create(array $data): int
    {
        return Database::insert('expenses', $data);
    }

    public static function monthTotal(?string $month = null): float
    {
        $month = $month ?? date('Y-m');
        $parts = explode('-', $month);
        $row = Database::fetch(
            'SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?',
            [(int) $parts[1], (int) $parts[0]]
        );
        return (float) ($row['total'] ?? 0);
    }

    public static function byCategory(?string $month = null): array
    {
        $month = $month ?? date('Y-m');
        $parts = explode('-', $month);
        return Database::fetchAll(
            'SELECT category, SUM(amount) as total FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ? GROUP BY category',
            [(int) $parts[1], (int) $parts[0]]
        );
    }
}
