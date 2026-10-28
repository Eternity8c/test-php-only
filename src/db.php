<?php

declare(strict_types=1);

function get_pdo(array $config): PDO
{
    $dsn = $config['db_dsn'];
    $user = $config['db_user'] ?: null;
    $pass = $config['db_pass'] ?: null;

    $pdo = new PDO($dsn, $user, $pass);
    return $pdo;
}
