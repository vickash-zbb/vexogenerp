<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\ActivityLog;
use App\Core\Auth;
use App\Core\Database;

class BackupService
{
    public static function create(): array
    {
        $cfg = require CONFIG_PATH . '/database.php';
        $dir = STORAGE_PATH . '/backups';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'vexogen_backup_' . date('Y-m-d_His') . '.sql';
        $path = $dir . '/' . $filename;

        $mysqldump = self::findMysqldump();
        if ($mysqldump) {
            $cmd = sprintf(
                '"%s" --host=%s --port=%s --user=%s %s %s > "%s" 2>&1',
                $mysqldump,
                $cfg['host'],
                $cfg['port'],
                $cfg['username'],
                $cfg['password'] !== '' ? '-p' . $cfg['password'] : '',
                $cfg['database'],
                $path
            );
            exec($cmd, $output, $code);
            if ($code !== 0 || !is_file($path) || filesize($path) < 100) {
                self::phpBackup($cfg, $path);
            }
        } else {
            self::phpBackup($cfg, $path);
        }

        self::pruneOldBackups($dir, 10);
        ActivityLog::write('backup', null, null, 'Database backup created: ' . $filename);

        return [
            'filename' => $filename,
            'path' => $path,
            'size' => filesize($path),
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public static function list(): array
    {
        $dir = STORAGE_PATH . '/backups';
        if (!is_dir($dir)) {
            return [];
        }
        $files = glob($dir . '/*.sql') ?: [];
        rsort($files);
        return array_map(function ($f) {
            return [
                'filename' => basename($f),
                'size' => filesize($f),
                'created_at' => date('Y-m-d H:i:s', filemtime($f)),
            ];
        }, $files);
    }

    public static function download(string $filename): ?string
    {
        $filename = basename($filename);
        $path = STORAGE_PATH . '/backups/' . $filename;
        return is_file($path) ? $path : null;
    }

    public static function verifyToken(?string $token): bool
    {
        $settings = Database::fetch('SELECT backup_token FROM company_settings LIMIT 1');
        $stored = $settings['backup_token'] ?? '';
        return $stored !== '' && hash_equals($stored, (string) $token);
    }

    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(24));
        $row = Database::fetch('SELECT id FROM company_settings LIMIT 1');
        if ($row) {
            Database::update('company_settings', ['backup_token' => $token], 'id = ?', [$row['id']]);
        }
        return $token;
    }

    private static function findMysqldump(): ?string
    {
        $paths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];
        foreach ($paths as $p) {
            if (is_file($p)) {
                return $p;
            }
        }
        $which = trim((string) shell_exec('where mysqldump 2>nul') ?: shell_exec('which mysqldump 2>/dev/null') ?: '');
        return $which !== '' ? explode("\n", $which)[0] : null;
    }

    private static function phpBackup(array $cfg, string $path): void
    {
        $pdo = Database::connection();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        $sql = "-- Vexogen CRM Backup\n-- " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $create['Create Table'] . ";\n\n";
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $row);
                $sql .= "INSERT INTO `{$table}` VALUES (" . implode(',', $vals) . ");\n";
            }
            $sql .= "\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($path, $sql);
    }

    private static function pruneOldBackups(string $dir, int $keep): void
    {
        $files = glob($dir . '/*.sql') ?: [];
        rsort($files);
        foreach (array_slice($files, $keep) as $old) {
            @unlink($old);
        }
    }
}
