<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Employee
{
    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT e.*, 
             (SELECT COUNT(*) FROM projects p WHERE p.assigned_employee_id = e.id AND p.status NOT IN ('completed','delivered','closed')) as active_projects
             FROM employees e WHERE e.status = 'active' ORDER BY e.name"
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM employees WHERE id = ?', [$id]);
    }

    public static function create(array $data): int
    {
        return Database::insert('employees', $data);
    }

    public static function dropdown(): array
    {
        return Database::fetchAll("SELECT id, name FROM employees WHERE status = 'active' ORDER BY name");
    }
}
