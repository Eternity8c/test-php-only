<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';


?>

<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <title>Главная страница</title>
</head>

<body>
    <?php if (isset($_SESSION['flash'])): ?>
        <p><?= htmlspecialchars($_SESSION['flash']) ?></p>
        <?php unset($_SESSION['flash']) ?>
    <?php endif ?>
    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Привет, <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></p>
        <p><a href="/profile.php">Мой профиль</a></p>
    <?php else: ?>
        <p><a href="/login.php">Войти</a> | <a href="/register.php">Регистрация</a></p>
    <?php endif ?>
</body>

</html>