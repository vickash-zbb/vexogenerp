<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Task
{
    public static function all(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 't.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['project_id'])) {
            $where[] = 't.project_id = ?';
            $params[] = $filters['project_id'];
        }
        if (!empty($filters['assigned_to'])) {
            $where[] = 't.assigned_to = ?';
            $params[] = $filters['assigned_to'];
        }
        $sql = "SELECT t.*, p.name as project_name, e.name as assignee_name
                FROM tasks t
                LEFT JOIN projects p ON p.id = t.project_id
                LEFT JOIN employees e ON e.id = t.assigned_to
                WHERE " . implode(' AND ', $where) . "
                ORDER BY t.position ASC, t.due_date ASC";
        return Database::fetchAll($sql, $params);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM tasks WHERE id = ?', [$id]);
    }

    public static function create(array $data): int
    {
        return Database::insert('tasks', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('tasks', $data, 'id = ?', [$id]);
    }

    public static function pending(int $limit = 5, array $filters = []): array
    {
        $where = ["t.status != 'done'"];
        $params = [];
        if (!empty($filters['priority'])) {
            $where[] = "t.priority = ?";
            $params[] = $filters['priority'];
        }
        $whereSql = implode(' AND ', $where);

        return Database::fetchAll(
            "SELECT t.*, e.name as assignee_name FROM tasks t
             LEFT JOIN employees e ON e.id = t.assigned_to
             WHERE $whereSql ORDER BY t.due_date ASC LIMIT {$limit}",
             $params
        );
    }

    public static function countPending(): int
    {
        $row = Database::fetch("SELECT COUNT(*) as cnt FROM tasks WHERE status != 'done'");
        return (int) ($row['cnt'] ?? 0);
    }
}
