<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Client
{
    public static function all(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'c.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['industry'])) {
            $where[] = 'c.industry = ?';
            $params[] = $filters['industry'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(c.company_name LIKE ? OR c.contact_person LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)';
            $q = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$q, $q, $q, $q]);
        }
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM projects p WHERE p.client_id = c.id AND p.status NOT IN ('completed','delivered','closed')) as active_projects
                FROM clients c WHERE " . implode(' AND ', $where) . " ORDER BY c.company_name ASC LIMIT {$perPage} OFFSET {$offset}";
        return Database::fetchAll($sql, $params);
    }

    public static function count(array $filters = []): int
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(company_name LIKE ? OR contact_person LIKE ? OR email LIKE ?)';
            $q = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$q, $q, $q]);
        }
        $row = Database::fetch('SELECT COUNT(*) as cnt FROM clients WHERE ' . implode(' AND ', $where), $params);
        return (int) ($row['cnt'] ?? 0);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM clients WHERE id = ?', [$id]);
    }

    public static function create(array $data): int
    {
        return Database::insert('clients', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('clients', $data, 'id = ?', [$id]);
    }

    public static function recalculateBalance(int $clientId): void
    {
        $row = Database::fetch(
            'SELECT COALESCE(SUM(pending_amount), 0) as pending FROM invoices WHERE client_id = ? AND status NOT IN ("paid","cancelled")',
            [$clientId]
        );
        Database::update('clients', ['outstanding_balance' => $row['pending'] ?? 0], 'id = ?', [$clientId]);
    }

    public static function dropdown(): array
    {
        return Database::fetchAll('SELECT id, company_name, address FROM clients WHERE status != "inactive" ORDER BY company_name');
    }
}
