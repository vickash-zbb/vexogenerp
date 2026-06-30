<?php

declare(strict_types=1);

return [
    'driver' => 'smtp',
    'host' => 'smtp.hostinger.com',
    'port' => 465,
    'username' => 'hello@vexogen.in',
    'password' => env('MAIL_PASSWORD', ''),
    'encryption' => 'ssl',
    'from_email' => 'hello@vexogen.in',
    'from_name' => 'Vexogen',
];
