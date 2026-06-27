<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\BackupService;

class SettingsController extends Controller
{
    public function index(): void
    {
        $settings = Database::fetch('SELECT * FROM company_settings LIMIT 1') ?: [];
        $users = Database::fetchAll('SELECT id, name, email, role, is_active, last_login_at FROM users ORDER BY name');
        $backups = BackupService::list();
        $cronBase = preg_replace('#/public/?$#', '', config('app.url'));
        $this->view('settings/index', [
            'title' => 'Settings',
            'page' => 'settings',
            'settings' => $settings,
            'users' => $users,
            'backups' => $backups,
            'cronUrl' => $cronBase . '/backup.php',
        ]);
    }
}
