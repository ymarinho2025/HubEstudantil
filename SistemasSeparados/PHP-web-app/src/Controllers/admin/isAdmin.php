<?php

$pdo = require __DIR__ . '/../db.php';
require_once __DIR__ . '/../login/key.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$auth_token = $_COOKIE['auth_token'] ?? null;

try {
    if (!$auth_token) {
        throw new Exception("Token não encontrado");
    }

    $decoded = JWT::decode($_COOKIE['auth_token'], new Key($key, 'HS256'));
    $roles = $decoded->roles;
    if (($roles) !== 3) {
        echo "<script>window.location.href = '/login.php';</script>";
        exit();
    }
} catch (Exception $e) {
    echo "<script>window.location.href = '/login.php';</script>";
    exit();
}

// Pega o IP do visitante
// $user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 
//     $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
//     $_SERVER['REMOTE_ADDR'];
// 
// Seu IP permitido
// $ip_permitido = '123.456.789.000'; // Substitua pelo seu IP real

// Verifica se o IP é permitido
// if ($user_ip !== $ip_permitido) {
//     die('Acesso negado!');
// }
// 
// Restante do seu código aqui...
// echo "Bem-vindo! Acesso permitido.";

?>