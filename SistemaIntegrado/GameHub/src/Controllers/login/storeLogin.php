<?php
$pdo = require __DIR__ . '/../../db.php';
require_once __DIR__ . '/key.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
use Firebase\JWT\JWT; use Firebase\JWT\Key;
$loginErro = '';
function clientIp(){ return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); $senha = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id,name,email,password,roles FROM users WHERE email=:email LIMIT 1');
    $stmt->execute([':email'=>$email]); $user = $stmt->fetch();
    $ok = false;
    if ($user) {
        $ok = password_verify($senha, $user['password']) || hash('sha256', $senha) === $user['password'];
        if ($ok && hash('sha256', $senha) === $user['password']) {
            $pdo->prepare('UPDATE users SET password=:p WHERE id=:id')->execute([':p'=>password_hash($senha, PASSWORD_DEFAULT), ':id'=>$user['id']]);
        }
    }
    if ($ok) {
        $pdo->prepare('INSERT INTO user_logins(user_id, ip) VALUES(:u,:ip)')->execute([':u'=>$user['id'], ':ip'=>substr(clientIp(),0,45)]);
        $jwt = JWT::encode(['iat'=>time(),'exp'=>time()+86400,'id'=>(int)$user['id'],'roles'=>(int)$user['roles']], $key, 'HS256');
        setcookie('auth_token', $jwt, ['expires'=>time()+86400,'path'=>'/','httponly'=>true,'secure'=>false,'samesite'=>'Lax']);
        header('Location: /home.php'); exit;
    }
    $loginErro = 'Email ou senha incorretos.';
} else if (!empty($_COOKIE['auth_token'])) {
    try { JWT::decode($_COOKIE['auth_token'], new Key($key, 'HS256')); header('Location: /home.php'); exit; } catch(Exception $e) {}
}
