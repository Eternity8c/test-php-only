<?php

declare(strict_types=1);

function verify_captcha(string $token, array $config, string $ip): bool
{
    if ($token === '' || $config['captcha_secret'] === '') {
        return false;
    }

    $ch = curl_init('https://smartcaptcha.cloud.yandex.ru/validate');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'secret' => $config['captcha_secret'],
            'token'  => $token,
            'ip'     => $ip,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $err !== '') {
        return false;
    }

    $data = json_decode($raw, true);

    return is_array($data) && ($data['status'] ?? '') === 'ok';
}
