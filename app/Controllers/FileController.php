<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;
use App\Models\FileModel;
use App\Models\Project;

class FileController extends Controller
{
    public function index(): void
    {
        $filters = array_filter([
            'project_id' => $this->input('project_id'),
            'client_id' => $this->input('client_id'),
            'search' => $this->input('q'),
        ]);
        $this->view('files/index', [
            'title' => 'File Manager',
            'page' => 'files',
            'files' => FileModel::all($filters),
            'projects' => Project::all([], 1, 200),
            'clients' => Client::dropdown(),
            'filters' => $filters,
        ]);
    }

    public function download(string $id): void
    {
        $file = FileModel::find((int) $id);
        if (!$file) {
            http_response_code(404);
            die('File not found');
        }
        $path = STORAGE_PATH . '/uploads/' . $file['file_path'];
        if (!is_file($path)) {
            http_response_code(404);
            die('File missing on disk');
        }
        header('Content-Type: ' . ($file['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
