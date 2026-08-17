<?php
$pdo = require __DIR__ . '/../db.php';

$usuarios = [];
$total_usuarios = 0;
$admins = 0;
$professores = 0;
$usuarios_comuns = 0;

$stmt = $pdo->query("SELECT id, name, email, roles FROM users ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_usuarios = count($usuarios);

foreach ($usuarios as $usuario) {
    $role = (int)$usuario['roles'];

    if ($role === 3) {
        $admins++;
    } elseif ($role === 2) {
        $professores++;
    } else {
        $usuarios_comuns++;
    }
}
