<?php
$pdo = require __DIR__ . '/../../db.php';
require_once __DIR__ . '/key.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
$auth_token = $_COOKIE['auth_token'] ?? null;
try {
    if (!$auth_token) throw new Exception('Token não encontrado');
    $decoded = JWT::decode($auth_token, new Key($key, 'HS256'));
    $userId = (int)$decoded->id;
    $stmt = $pdo->prepare('SELECT id, name, email, roles FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id'=>$userId]);
    $authUser = $stmt->fetch();
    if (!$authUser) throw new Exception('Usuário não encontrado');
    $userName = $authUser['name'];
    $role = (int)$authUser['roles'];
} catch (Exception $e) {
    setcookie('auth_token', '', ['expires'=>time()-3600,'path'=>'/','httponly'=>true,'secure'=>false,'samesite'=>'Lax']);
    header('Location: /login.php'); exit;
}
