<?php
$pdo = require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

    $id = $_POST['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        header("Location: admin.php?msg=id_invalido");
        exit();
    }

    $id = (int)$id;

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([
            ':id' => $id
        ]);

        header("Location: admin.php?msg=deletado");
        exit();
    } catch (PDOException $e) {
        header("Location: admin.php?msg=erro_delete");
        exit();
    }
}
?>
