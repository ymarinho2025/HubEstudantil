<?php

$pdo = require __DIR__ . '/../db.php';
require_once __DIR__ . '/key.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$auth_token = $_COOKIE['auth_token'] ?? null;

try {
    if (!$auth_token) {
        throw new Exception("Token não encontrado");
    }

    $decoded = JWT::decode($auth_token, new Key($key, 'HS256'));
    $userId = (int) $decoded->id;

    $stmtUser = $pdo->prepare("SELECT name FROM users WHERE id = :id LIMIT 1");
    $stmtUser->execute([
        ':id' => $userId
    ]);

    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Usuário não encontrado");
    }

    $userName = $user['name'];

} catch (Exception $e) {
    setcookie("auth_token", "", time() - 3600, "/");

    echo "<script>window.location.href = '/login.php';</script>";
    exit();
}
