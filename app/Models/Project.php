<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Project
{
    public static function all(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['client_id'])) {
            $where[] = 'p.client_id = ?';
            $params[] = $filters['client_id'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'p.category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE ? OR p.project_code LIKE ? OR c.company_name LIKE ?)';
            $q = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$q, $q, $q]);
        }
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, c.company_name as client_name, e.name as assigned_name, e.id as emp_id
                FROM projects p
                JOIN clients c ON c.id = p.client_id
                LEFT JOIN employees e ON e.id = p.assigned_employee_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.updated_at DESC LIMIT {$perPage} OFFSET {$offset}";
        return Database::fetchAll($sql, $params);
    }

    public static function byStatus(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['month'])) {
            $dateStart = $filters['month'] . '-01';
            $dateEnd = date('Y-m-t', strtotime($dateStart));
            $where[] = 'created_at BETWEEN ? AND ?';
            $params[] = $dateStart . ' 00:00:00';
            $params[] = $dateEnd . ' 23:59:59';
        }
        $rows = Database::fetchAll('SELECT status, COUNT(*) as cnt FROM projects WHERE ' . implode(' AND ', $where) . ' GROUP BY status', $params);
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['status']] = (int) $r['cnt'];
        }
        return $grouped;
    }

    public static function workspaceSummary(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['client_id'])) {
            $where[] = 'p.client_id = ?';
            $params[] = $filters['client_id'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'p.category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE ? OR p.project_code LIKE ? OR c.company_name LIKE ?)';
            $q = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$q, $q, $q]);
        }

        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN p.status NOT IN ('completed','delivered','closed') THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN p.expected_delivery < CURDATE() AND p.status NOT IN ('completed','delivered','closed') THEN 1 ELSE 0 END) as overdue,
                    SUM(CASE WHEN p.expected_delivery BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                        AND p.status NOT IN ('completed','delivered','closed') THEN 1 ELSE 0 END) as due_soon,
                    ROUND(COALESCE(AVG(p.completion_percentage), 0)) as avg_progress,
                    COALESCE(SUM(p.selling_price), 0) as pipeline_value
                FROM projects p
                JOIN clients c ON c.id = p.client_id
                WHERE " . implode(' AND ', $where);
        $row = Database::fetch($sql, $params) ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'overdue' => (int) ($row['overdue'] ?? 0),
            'due_soon' => (int) ($row['due_soon'] ?? 0),
            'avg_progress' => (int) ($row['avg_progress'] ?? 0),
            'pipeline_value' => (float) ($row['pipeline_value'] ?? 0),
        ];
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT p.*, c.company_name as client_name, e.name as assigned_name
             FROM projects p JOIN clients c ON c.id = p.client_id
             LEFT JOIN employees e ON e.id = p.assigned_employee_id WHERE p.id = ?',
            [$id]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert('projects', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('projects', $data, 'id = ?', [$id]);
    }

    public static function stats(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['month'])) {
            $dateStart = $filters['month'] . '-01';
            $dateEnd = date('Y-m-t', strtotime($dateStart));
            $where[] = 'created_at BETWEEN ? AND ?';
            $params[] = $dateStart . ' 00:00:00';
            $params[] = $dateEnd . ' 23:59:59';
        }
        $whereSql = implode(' AND ', $where);

        $total = Database::fetch("SELECT COUNT(*) as cnt FROM projects WHERE $whereSql", $params);
        
        $activeWhere = $where;
        $activeWhere[] = "status NOT IN ('completed','delivered','closed')";
        $active = Database::fetch("SELECT COUNT(*) as cnt FROM projects WHERE " . implode(' AND ', $activeWhere), $params);
        
        $compWhere = $where;
        $compWhere[] = "status IN ('completed','delivered','closed')";
        $completed = Database::fetch("SELECT COUNT(*) as cnt FROM projects WHERE " . implode(' AND ', $compWhere), $params);
        
        $pendWhere = $where;
        $pendWhere[] = "status IN ('lead','discussion','quotation_sent')";
        $pending = Database::fetch("SELECT COUNT(*) as cnt FROM projects WHERE " . implode(' AND ', $pendWhere), $params);
        
        $upcWhere = $where;
        $upcWhere[] = "expected_delivery BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $upcWhere[] = "status NOT IN ('completed','delivered','closed')";
        $upcoming = Database::fetch("SELECT COUNT(*) as cnt FROM projects WHERE " . implode(' AND ', $upcWhere), $params);

        return [
            'total' => (int) ($total['cnt'] ?? 0),
            'active' => (int) ($active['cnt'] ?? 0),
            'completed' => (int) ($completed['cnt'] ?? 0),
            'pending' => (int) ($pending['cnt'] ?? 0),
            'upcoming_deliveries' => (int) ($upcoming['cnt'] ?? 0),
        ];
    }

    public static function recent(int $limit = 5, array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['month'])) {
            $dateStart = $filters['month'] . '-01';
            $dateEnd = date('Y-m-t', strtotime($dateStart));
            $where[] = 'p.created_at BETWEEN ? AND ?';
            $params[] = $dateStart . ' 00:00:00';
            $params[] = $dateEnd . ' 23:59:59';
        }
        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }
        $whereSql = implode(' AND ', $where);

        return Database::fetchAll(
            "SELECT p.*, c.company_name as client_name FROM projects p
             JOIN clients c ON c.id = p.client_id WHERE $whereSql ORDER BY p.updated_at DESC LIMIT {$limit}",
             $params
        );
    }
}
