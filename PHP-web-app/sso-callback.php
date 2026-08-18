<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$token = $_POST['sso_token'] ?? '';

if (!$token) {
    header('Location: https://hubestudantil.vercel.app');
    exit;
}

try {

    $secret = getenv('JWT_SECRET');

    if (!$secret) {
        throw new Exception('JWT_SECRET não configurado.');
    }

    $payload = JWT::decode(
        $token,
        new Key($secret, 'HS256')
    );

    if (($payload->type ?? '') !== 'sso') {
        throw new Exception('Token inválido.');
    }

    /*
     * Agora criamos um token normal de autenticação
     * deste domínio.
     */

    $now = time();

    $authPayload = [
        'iat' => $now,
        'exp' => $now + (60 * 60 * 24 * 7),
        'sub' => $payload->sub,
        'email' => $payload->email ?? '',
        'username' => $payload->username ?? ''
    ];

    $authToken = JWT::encode(
        $authPayload,
        $secret,
        'HS256'
    );

    setcookie('auth_token', $authToken, [
        'expires' => time() + (60 * 60 * 24 * 7),
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    header('Location: /home.php');
    exit;

} catch (Throwable $e) {

    header('Location: https://hubestudantil.vercel.app');
    exit;
}