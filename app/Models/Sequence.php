<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Sequence
{
    public static function next(string $type, string $prefix): string
    {
        $year = (int) date('Y');
        Database::beginTransaction();
        try {
            $row = Database::fetch(
                'SELECT * FROM invoice_sequences WHERE type = ? AND year = ? FOR UPDATE',
                [$type, $year]
            );
            if (!$row) {
                Database::insert('invoice_sequences', [
                    'type' => $type,
                    'year' => $year,
                    'last_number' => 1,
                ]);
                $num = 1;
            } else {
                $num = (int) $row['last_number'] + 1;
                Database::update('invoice_sequences', ['last_number' => $num], 'id = ?', [$row['id']]);
            }
            Database::commit();
            return sprintf('%s-%d-%03d', $prefix, $year, $num);
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
