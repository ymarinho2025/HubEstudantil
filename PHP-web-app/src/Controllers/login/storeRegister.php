<?php

$pdo = require __DIR__ . '/../db.php';
require_once __DIR__ . '/key.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

$email = trim($_POST['email'] ?? '');
$nome  = trim($_POST['name'] ?? '');
$senha = $_POST['password'] ?? '';
$hash  = hash('sha256', $senha);

$stmt = $pdo->prepare("SELECT email FROM users WHERE email = :email LIMIT 1");
$stmt->execute([
    ':email' => $email
]);

$userExists = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userExists) {
    // Email já cadastrado
} else {
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password)
        VALUES (:name, :email, :password)
    ");

    $stmt->execute([
        ':name'     => $nome,
        ':email'    => $email,
        ':password' => $hash
    ]);
}

$auth_token = $_COOKIE['auth_token'] ?? null;

if ($auth_token) {
    echo "<script>window.location.href = '/home.php';</script>";
    exit();
}
?>
