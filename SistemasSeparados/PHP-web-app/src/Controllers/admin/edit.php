<?php

$pdo = require __DIR__ . '/../db.php';
require_once __DIR__ . '/../login/key.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $id = $_POST['edit_id'] ?? null;
    $role = $_POST['edit_role'] ?? null;

    if (!$id || !is_numeric($id)) {
        header("Location: admin.php?msg=id_invalido");
        exit();
    }

    if (!in_array((int)$role, [1, 2, 3], true)) {
        header("Location: admin.php?msg=role_invalida");
        exit();
    }

    $id = (int)$id;
    $role = (int)$role;

    $decoded = JWT::decode($_COOKIE['auth_token'], new Key($key, 'HS256'));
    $userId = (int) $decoded->id;

    if ($userId === $id) {
        header("Location: admin.php?msg=nao_pode_alterar_proprio_role");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET roles = :roles WHERE id = :id");
        $stmt->execute([
            ':roles' => $role,
            ':id'    => $id
        ]);

        header("Location: admin.php?msg=role_atualizada");
        exit();
    } catch (PDOException $e) {
        header("Location: admin.php?msg=erro_update");
        exit();
    }
}

function nomeRole($role) {
    $role = (int)$role;

    if ($role === 3) {
        return 'Administrador';
    } elseif ($role === 2) {
        return 'Professor';
    } else {
        return 'Usuário';
    }
}
?>
