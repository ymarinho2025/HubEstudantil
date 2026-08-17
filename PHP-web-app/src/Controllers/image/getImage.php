<?php
$pdo = require __DIR__ . '/../db.php';
require_once __DIR__ . '/../login/key.php';
require_once __DIR__ . '/base64.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$imagens = [];
$userId = null;

if (isset($_COOKIE['auth_token'])) {
    try {
        $decoded = JWT::decode($_COOKIE['auth_token'], new Key($key, 'HS256'));
        $userId = (int) $decoded->id;
    } catch (Exception $e) {
        die("Usuário não autenticado.");
    }
}

if (
    isset($_POST['mes']) &&
    isset($_POST['ano']) &&
    $userId !== null
) {
    $mes = (int) $_POST['mes'];
    $ano = (int) $_POST['ano'];

    $stmt = $pdo->prepare("
        SELECT nome, imagem
        FROM midia
        WHERE mes = :mes
          AND ano = :ano
          AND user_id = :user_id
        ORDER BY id DESC
    ");

    $stmt->execute([
        ':mes'     => $mes,
        ':ano'     => $ano,
        ':user_id' => $userId
    ]);

    $imagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
