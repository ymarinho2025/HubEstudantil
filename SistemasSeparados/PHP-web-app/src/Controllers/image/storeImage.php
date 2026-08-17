<?php
$pdo = require __DIR__ . '/../db.php';
require_once __DIR__ . '/../login/key.php';
require_once __DIR__ . '/base64.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
    isset($_POST['namefotos']) &&
    isset($_POST['mes']) &&
    isset($_POST['ano']) &&
    isset($_POST['upload']) &&
    isset($_FILES['fotos']) &&
    $_FILES['fotos']['error'] === UPLOAD_ERR_OK &&
    $userId !== null
) {
    $nome = trim($_POST['namefotos']);
    $mes = (int) $_POST['mes'];
    $ano = (int) $_POST['ano'];

    if ($base64Image) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO midia (nome, mes, ano, imagem, user_id)
                VALUES (:nome, :mes, :ano, :imagem, :user_id)
            ");

            $stmt->execute([
                ':nome'    => $nome,
                ':mes'     => $mes,
                ':ano'     => $ano,
                ':imagem'  => $base64Image,
                ':user_id' => $userId
            ]);

            echo "Midia salva com sucesso!";
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                echo "Já existe uma mídia com esse nome nessa mesma data.";
            } else {
                echo "Erro ao registrar Midia: " . $e->getMessage();
            }
        }
    } else {
        echo "Erro: a imagem não foi convertida para base64.";
    }
}
?>
