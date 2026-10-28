<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';


if (isset($_SESSION['user_id'])) {
    header('Location: /profile.php');
    exit;
}

$errors = [];
$old = ['login' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Капча — первой, до любого обращения к паролю и к базе.
    // Иначе ответы на верный и неверный пароль различаются, и бот
    // перебирает пароли, ни разу не пройдя капчу.
    if (!verify_captcha($_POST['smart-token'] ?? '', $config, $_SERVER['REMOTE_ADDR'])) {
        $errors[] = 'Подтвердите, что вы не робот';
    }

    $old['login'] = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($old['login'] === '') {
        $errors[] = 'Телефон или email не может быть пустым';
    }
    if ($password === '') {
        $errors[] = 'Пароль не может быть пустым';
    }

    $input = $old['login'];
    if (str_contains($input, '@')) {
        $old['login'] = strtolower($input);
    } else {
        $old['login'] =  preg_replace('/\D+/', '', $input);
        if ($old['login'] !== '' && $old['login'][0] === '8') {
            $old['login'] = '7' . substr($old['login'], 1);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errors === []) {
    $st = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = :login OR phone = :login');
    $st->execute(['login' => $old['login']]);
    $user = $st->fetch();
    if ($user === false || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Неверный логин или пароль';
    } else {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: /profile.php');
        exit;
    }
}

?>

<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <title>Авторизация</title>
    <script src="https://smartcaptcha.cloud.yandex.ru/captcha.js" defer></script>
</head>

<body>
    <h1>Авторизация</h1>

    <?php foreach ($errors as $error): ?>
        <p style="color:red">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endforeach; ?>

    <form method="post">
        <p>Телефон или email: <input type="text" name="login" value="<?= htmlspecialchars($old['login']) ?>"></p>
        <p>Пароль: <input type="password" name="password"></p>
        <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
        <div class="smart-captcha" data-sitekey="<?= htmlspecialchars($config['captcha_sitekey']) ?>"></div>
        <button type="submit">Авторизироваться</button>
    </form>
    <div
        id="captcha-container"
        class="smart-captcha"
        data-sitekey="ysc1_pjvPq9vEIfU9aIIBr5dkR5LDdppyqB4MBc8o4xQOa1f03c0d">
    </div>
</body>

</html>