<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Search
{
    public static function global(string $query, int $limit = 5): array
    {
        $q = '%' . $query . '%';
        return [
            'clients' => Database::fetchAll(
                "SELECT id, company_name as title, contact_person as subtitle, 'client' as type FROM clients
                 WHERE company_name LIKE ? OR contact_person LIKE ? OR email LIKE ? LIMIT {$limit}",
                [$q, $q, $q]
            ),
            'projects' => Database::fetchAll(
                "SELECT p.id, p.name as title, c.company_name as subtitle, 'project' as type FROM projects p
                 JOIN clients c ON c.id = p.client_id WHERE p.name LIKE ? OR p.project_code LIKE ? LIMIT {$limit}",
                [$q, $q]
            ),
            'invoices' => Database::fetchAll(
                "SELECT i.id, i.invoice_number as title, c.company_name as subtitle, 'invoice' as type FROM invoices i
                 JOIN clients c ON c.id = i.client_id WHERE i.invoice_number LIKE ? LIMIT {$limit}",
                [$q]
            ),
            'tasks' => Database::fetchAll(
                "SELECT id, title, status as subtitle, 'task' as type FROM tasks WHERE title LIKE ? LIMIT {$limit}",
                [$q]
            ),
            'employees' => Database::fetchAll(
                "SELECT id, name as title, designation as subtitle, 'employee' as type FROM employees WHERE name LIKE ? OR email LIKE ? LIMIT {$limit}",
                [$q, $q]
            ),
            'files' => Database::fetchAll(
                "SELECT id, original_name as title, extension as subtitle, 'file' as type FROM files WHERE original_name LIKE ? LIMIT {$limit}",
                [$q]
            ),
        ];
    }
}
