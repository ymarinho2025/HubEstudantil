<?php
$pdo = require __DIR__ . '/../db.php';
require_once __DIR__ . '/key.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getClientIpAddress()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function saveUserLoginIp(PDO $pdo, int $userId, string $ip): void
{
    $stmt = $pdo->prepare("
        INSERT INTO user_logins (user_id, ip)
        VALUES (:user_id, :ip)
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':ip'      => $ip
    ]);
}

// Só tenta logar se for POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['password'] ?? '';
    $hash  = hash('sha256', $senha);

    $stmt = $pdo->prepare("
        SELECT id, name, email, password, roles
        FROM users
        WHERE email = :email
          AND password = :password
        LIMIT 1
    ");

    $stmt->execute([
        ':email'    => $email,
        ':password' => $hash
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $ip = getClientIpAddress();
        saveUserLoginIp($pdo, (int) $user['id'], $ip);

        $payload = [
            "iat"   => time(),
            "exp"   => time() + 3600,
            "id"    => (int) $user['id'],
            "roles" => (int) $user['roles']
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');

        setcookie("auth_token", $jwt, [
            'expires'  => time() + 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => false,
            'samesite' => 'Lax'
        ]);

        header('Location: /home.php');
        exit();

    } else {
        $loginErro = "Email ou senha incorretos.";
    }

} else {
    $auth_token = $_COOKIE['auth_token'] ?? null;

    if ($auth_token) {
        try {
            JWT::decode($auth_token, new Key($key, 'HS256'));
            header('Location: /home.php');
            exit();
        } catch (Exception $e) {
            setcookie("auth_token", "", [
                'expires'  => time() - 3600,
                'path'     => '/',
                'httponly' => true,
                'secure'   => false,
                'samesite' => 'Lax'
            ]);
            unset($_COOKIE['auth_token']);
        }
    }
}
