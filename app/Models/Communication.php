<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

class Communication
{
    public static function forClient(int $clientId, int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT cm.*, u.name as user_name FROM communications cm
             LEFT JOIN users u ON u.id = cm.user_id
             WHERE cm.client_id = ? ORDER BY cm.created_at DESC LIMIT {$limit}",
            [$clientId]
        );
    }

    public static function create(array $data): int
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        return Database::insert('communications', $data);
    }
}
