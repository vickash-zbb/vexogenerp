<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Quotation
{
    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT q.*, c.company_name as client_name,
             (SELECT GROUP_CONCAT(service_name SEPARATOR ', ') FROM quotation_items WHERE quotation_id = q.id LIMIT 3) as services
             FROM quotations q JOIN clients c ON c.id = q.client_id ORDER BY q.created_at DESC"
        );
    }

    public static function find(int $id): ?array
    {
        $q = Database::fetch('SELECT q.*, c.company_name FROM quotations q JOIN clients c ON c.id = q.client_id WHERE q.id = ?', [$id]);
        if ($q) {
            $q['items'] = Database::fetchAll('SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY position', [$id]);
        }
        return $q;
    }

    public static function create(array $data, array $items): int
    {
        Database::beginTransaction();
        try {
            $id = Database::insert('quotations', $data);
            foreach ($items as $i => $item) {
                $item['quotation_id'] = $id;
                $item['position'] = $i;
                Database::insert('quotation_items', $item);
            }
            Database::commit();
            return $id;
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
