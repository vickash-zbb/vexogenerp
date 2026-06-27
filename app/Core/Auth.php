<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    private const SESSION_KEY = 'user';

    public static function attempt(string $email, string $password): bool
    {
        $user = Database::fetch(
            'SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1',
            [$email]
        );
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        unset($user['password']);
        $_SESSION[self::SESSION_KEY] = $user;
        Database::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        ActivityLog::write('login', 'user', (int) $user['id'], 'User logged in');
        return true;
    }

    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION[self::SESSION_KEY]['id']) ? (int) $_SESSION[self::SESSION_KEY]['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function logout(): void
    {
        if (self::check()) {
            ActivityLog::write('logout', 'user', self::id(), 'User logged out');
        }
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    public static function hasRole(string|array $roles): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        $roles = (array) $roles;
        return in_array($user['role'], $roles, true);
    }

    public static function can(string $permission): bool
    {
        $role = self::user()['role'] ?? '';
        $permissions = [
            'admin' => ['*'],
            'manager' => ['clients','projects','tasks','payments','invoices','quotations','expenses','employees','reports','calendar','files'],
            'designer' => ['projects','tasks','calendar','files'],
            'developer' => ['projects','tasks','calendar','files'],
            'marketing' => ['clients','projects','tasks','quotations','calendar'],
            'accounts' => ['clients','payments','invoices','quotations','expenses','reports'],
            'client' => ['projects'],
        ];
        $allowed = $permissions[$role] ?? [];
        return in_array('*', $allowed, true) || in_array($permission, $allowed, true);
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            if (self::isApiRequest()) {
                json_response(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            redirect('login');
        }
    }

    public static function requirePermission(string $permission): void
    {
        self::requireAuth();
        if (!self::can($permission)) {
            if (self::isApiRequest()) {
                json_response(['success' => false, 'message' => 'Forbidden'], 403);
            }
            http_response_code(403);
            die('Access denied.');
        }
    }

    private static function isApiRequest(): bool
    {
        return str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')
            || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }
}
