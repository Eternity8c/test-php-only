<?php

declare(strict_types=1);

require __DIR__ . "/../src/bootstrap.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

$id = (int) $_SESSION['user_id'];

$st = $pdo->prepare('SELECT id, name, email, phone, password_hash FROM users WHERE id = :id');
$st->execute(['id' => $id]);
$user = $st->fetch();
if ($user === false) {
    $_SESSION = [];
    session_destroy();
    header('Location: /index.php');
    exit;
}

$errors = [];
$old = [
    'name' => $user['name'],
    'email' => $user['email'],
    'phone' => $user['phone'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = strtolower(trim($_POST['email'] ?? ''));
    $old['phone'] = trim($_POST['phone'] ?? '');
    $newPassword = $_POST['password'] ?? '';

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
    if ($errors === []) {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id');
        $st->execute(['email' => $old['email'], 'id' => $id]);
        if ($st->fetch()) {
            $errors[] = 'email уже зарегистрирован';
        }

        $st = $pdo->prepare('SELECT id FROM users WHERE phone = :phone AND id <> :id');
        $st->execute(['phone' => $old['phone'], 'id' => $id]);
        if ($st->fetch()) {
            $errors[] = 'Такой телефон уже зарегистрирован';
        }
    }

    if ($errors === []) {
        try {
            if ($newPassword !== '') {
                $st = $pdo->prepare('UPDATE users SET name=:name, email=:email, phone=:phone, password_hash=:hash WHERE id=:id');
                $st->execute([
                    'name' => $old['name'],
                    'email' => $old['email'],
                    'phone' => $old['phone'],
                    'hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                    'id' => $id,
                ]);
            } else {
                $st = $pdo->prepare('UPDATE users SET name=:name, email=:email, phone=:phone WHERE id=:id');
                $st->execute([
                    'name' => $old['name'],
                    'email' => $old['email'],
                    'phone' => $old['phone'],
                    'id' => $id,
                ]);
            }
            $_SESSION['user_name'] = $old['name'];
            $_SESSION['flash'] = 'Данные обновлены';
            header('Location: /profile.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Такой email или телефон уже занят';
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
    <title>Профиль</title>
</head>

<body>
    <h1>Профиль</h1>

    <?php foreach ($errors as $error): ?>
        <p style="color:red">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endforeach; ?>
    <?php if (isset($_SESSION['flash'])): ?>
        <p><?= htmlspecialchars($_SESSION['flash']) ?></p>
        <?php unset($_SESSION['flash']) ?>
    <?php endif ?>
    <form method="post">
        <p>Имя: <input type="text" name="name" value="<?= htmlspecialchars($old['name']) ?>"></p>
        <p>Телефон: <input type="tel" name="phone" value="<?= htmlspecialchars($old['phone']) ?>"></p>
        <p>Email: <input type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>"></p>
        <p>Пароль: <input type="password" name="password"></p>
        <button type="submit">Изменить данные</button>
    </form>
</body>

</html>