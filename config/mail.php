<?php

declare(strict_types=1);

return [
    'driver' => env('MAIL_DRIVER', 'smtp'),
    'host' => env('MAIL_HOST', ''),
    'port' => (int) env('MAIL_PORT', 587),
    'username' => env('MAIL_USERNAME', ''),
    'password' => env('MAIL_PASSWORD', ''),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'from_email' => env('MAIL_FROM', 'hello@vexogen.com'),
    'from_name' => env('MAIL_FROM_NAME', 'Vexogen'),
];
