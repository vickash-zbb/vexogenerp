<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

class FileModel
{
    public static function all(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['project_id'])) {
            $where[] = 'f.project_id = ?';
            $params[] = $filters['project_id'];
        }
        if (!empty($filters['client_id'])) {
            $where[] = 'f.client_id = ?';
            $params[] = $filters['client_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(f.original_name LIKE ? OR p.name LIKE ? OR c.company_name LIKE ?)';
            $q = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$q, $q, $q]);
        }
        return Database::fetchAll(
            "SELECT f.*, p.name as project_name, c.company_name as client_name, u.name as uploader_name
             FROM files f
             LEFT JOIN projects p ON p.id = f.project_id
             LEFT JOIN clients c ON c.id = f.client_id
             LEFT JOIN users u ON u.id = f.uploaded_by
             WHERE " . implode(' AND ', $where) . " ORDER BY f.created_at DESC",
            $params
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM files WHERE id = ?', [$id]);
    }

    public static function create(array $data): int
    {
        return Database::insert('files', $data);
    }

    public static function delete(int $id): bool
    {
        $file = self::find($id);
        if (!$file) {
            return false;
        }
        $fullPath = STORAGE_PATH . '/uploads/' . $file['file_path'];
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
        Database::delete('files', 'id = ?', [$id]);
        return true;
    }

    public static function storeUpload(array $file, ?int $projectId, ?int $clientId): int
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = config('app.allowed_extensions', []);
        if (!in_array($ext, $allowed, true)) {
            throw new \InvalidArgumentException('File type not allowed: ' . $ext);
        }
        $maxBytes = (int) config('app.upload_max_mb', 50) * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            throw new \InvalidArgumentException('File exceeds maximum size.');
        }

        $subdir = $projectId ? 'projects/' . $projectId : ($clientId ? 'clients/' . $clientId : 'general');
        $dir = STORAGE_PATH . '/uploads/' . $subdir;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $stored = uniqid('f_', true) . '.' . $ext;
        $relative = $subdir . '/' . $stored;
        if (!move_uploaded_file($file['tmp_name'], STORAGE_PATH . '/uploads/' . $relative)) {
            throw new \RuntimeException('Failed to store uploaded file.');
        }

        return self::create([
            'filename' => $stored,
            'original_name' => $file['name'],
            'file_path' => $relative,
            'file_size' => (int) $file['size'],
            'mime_type' => $file['type'] ?? null,
            'extension' => $ext,
            'project_id' => $projectId,
            'client_id' => $clientId,
            'uploaded_by' => Auth::id(),
        ]);
    }

    public static function iconClass(string $ext): string
    {
        $map = [
            'pdf' => 'ti-file-type-pdf', 'png' => 'ti-photo', 'jpg' => 'ti-photo', 'jpeg' => 'ti-photo',
            'psd' => 'ti-brush', 'ai' => 'ti-brush', 'cdr' => 'ti-vector', 'zip' => 'ti-file-zip',
            'docx' => 'ti-file-text', 'mp4' => 'ti-video', 'svg' => 'ti-vector-triangle',
        ];
        return $map[$ext] ?? 'ti-file';
    }
}
