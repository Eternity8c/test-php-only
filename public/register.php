<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$errors = [];
$old = ['name' => '', 'phone' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['email'] = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $passwordRepeat = $_POST['password_repeat'] ?? '';

    if ($old['name'] === '') {
        $errors[] = 'имя не может быть пустым';
    }

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'неверная форма email';
    }

    $digits = preg_replace('/\D+/', '', $old['phone']);
    if ($digits !== '' && $digits[0] === '8') {
        $digits = '7' . substr($digits, 1);
    }

    if (strlen($digits) !== 11) {
        $errors[] = 'Телефон должен содержать 11 цифр';
    }

    $old['phone'] = $digits;

    if ($password === '') {
        $errors[] = 'пароль не может быть пустым';
    }

    if ($passwordRepeat !== $password) {
        $errors[] = 'Пароли не равны';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errors === []) {
    $st = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $st->execute(['email' => $old['email']]);
    if ($st->fetch()) {
        $errors[] = 'Такой email уже зарегистрирован';
    }

    $st = $pdo->prepare('SELECT id FROM users WHERE phone = :phone');
    $st->execute(['phone' => $old['phone']]);
    if ($st->fetch()) {
        $errors[] = 'Такой телефон уже зарегистрирован';
    }

    if ($errors === []) {
        try {
            $st = $pdo->prepare(
                'INSERT INTO users (name, email, phone, password_hash) VALUES (:name, :email, :phone, :hash)'
            );
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $st->execute([
                'name' => $old['name'],
                'email' => $old['email'],
                'phone' => $old['phone'],
                'hash' => $hash,
            ]);
            $_SESSION['flash'] = 'Регистрация прошла успешно';
            header('Location: /index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Такой email или телефон уже зарегистрирован';
            } else {
                throw $e;
            }
        }
    }
}

?>

<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <title>Регистрация</title>
</head>

<body>
    <h1>Регистрация</h1>

    <?php foreach ($errors as $error): ?>
        <p style="color:red">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endforeach; ?>

    <form method="post">
        <p>Имя: <input type="text" name="name" value="<?= htmlspecialchars($old['name']) ?>"></p>
        <p>Телефон: <input type="tel" name="phone" value="<?= htmlspecialchars($old['phone']) ?>"></p>
        <p>Email: <input type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>"></p>
        <p>Пароль: <input type="password" name="password"></p>
        <p>Повторите пароль: <input type="password" name="password_repeat"></p>
        <button type="submit">Зарегистрироваться</button>
    </form>
</body>

</html>