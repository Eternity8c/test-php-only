<?php

declare(strict_types=1);

return [
    'db_dsn' => getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=php_test;charset=utf8mb4',
    'db_user' => getenv('DB_USER') ?: 'app',
    'db_pass' => getenv('DB_PASS') ?: 'apppass',
    'captcha_secret' => getenv('CAPTCHA_SECRET') ?: '',
    'captcha_sitekey' => getenv('CAPTCHA_SITEKEY') ?: ''
];
