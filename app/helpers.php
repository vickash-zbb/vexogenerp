<?php

declare(strict_types=1);

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        $parts = explode('.', $key, 2);
        $file = $parts[0];
        if (!isset($configs[$file])) {
            $path = CONFIG_PATH . '/' . $file . '.php';
            $configs[$file] = is_file($path) ? require $path : [];
        }
        if (!isset($parts[1])) {
            return $configs[$file] ?? $default;
        }
        return $configs[$file][$parts[1]] ?? $default;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(config('app.url'), '/');
        $path = ltrim($path, '/');
        return $path === '' ? $base : $base . '/' . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('format_money')) {
    function format_money(float|int|string $amount, bool $compact = false): string
    {
        $amount = (float) $amount;
        $symbol = config('app.currency_symbol', '₹');
        if ($compact) {
            if ($amount >= 10000000) {
                return $symbol . number_format($amount / 10000000, 1) . 'Cr';
            }
            if ($amount >= 100000) {
                return $symbol . number_format($amount / 100000, 1) . 'L';
            }
            if ($amount >= 1000) {
                return $symbol . number_format($amount / 1000, 1) . 'K';
            }
        }
        return $symbol . number_format($amount, 0, '.', ',');
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'M j, Y'): string
    {
        if (!$date) {
            return '—';
        }
        return date($format, strtotime($date));
    }
}

if (!function_exists('amount_in_words')) {
    function amount_in_words(float|int|string $amount): string
    {
        $number = (int) round((float) $amount);
        if ($number === 0) {
            return 'Indian Rupees Zero Only';
        }

        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
            15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen',
        ];
        $tens = [
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
            60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety',
        ];

        $underThousand = static function (int $value) use ($ones, $tens): string {
            $parts = [];
            if ($value >= 100) {
                $parts[] = $ones[intdiv($value, 100)] . ' Hundred';
                $value %= 100;
            }
            if ($value >= 20) {
                $parts[] = $tens[intdiv($value, 10) * 10];
                $value %= 10;
            }
            if ($value > 0) {
                $parts[] = $ones[$value];
            }
            return implode(' ', $parts);
        };

        $parts = [];
        $groups = [
            10000000 => 'Crore',
            100000 => 'Lakh',
            1000 => 'Thousand',
        ];
        foreach ($groups as $divisor => $label) {
            if ($number >= $divisor) {
                $value = intdiv($number, $divisor);
                $parts[] = $underThousand($value) . ' ' . $label;
                $number %= $divisor;
            }
        }
        if ($number > 0) {
            $parts[] = $underThousand($number);
        }

        return 'Indian Rupees ' . implode(' ', $parts) . ' Only';
    }
}

if (!function_exists('status_label')) {
    function status_label(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status));
    }
}

if (!function_exists('status_badge_class')) {
    function status_badge_class(string $status): string
    {
        $map = [
            'active' => 'badge-green', 'paid' => 'badge-green', 'completed' => 'badge-green',
            'delivered' => 'badge-green', 'done' => 'badge-green', 'received' => 'badge-green',
            'accepted' => 'badge-green', 'present' => 'badge-green',
            'design' => 'badge-blue', 'development' => 'badge-blue', 'planning' => 'badge-blue',
            'sent' => 'badge-blue', 'in_progress' => 'badge-blue', 'todo' => 'badge-gray',
            'review' => 'badge-orange', 'revision' => 'badge-purple', 'partial' => 'badge-orange',
            'pending' => 'badge-orange', 'follow_up' => 'badge-orange', 'medium' => 'badge-orange',
            'overdue' => 'badge-red', 'high' => 'badge-red', 'urgent' => 'badge-red',
            'lead' => 'badge-gray', 'draft' => 'badge-gray', 'inactive' => 'badge-gray',
        ];
        return $map[$status] ?? 'badge-gray';
    }
}

if (!function_exists('json_response')) {
    function json_response(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        return e($_SESSION['_old'][$key] ?? $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}
