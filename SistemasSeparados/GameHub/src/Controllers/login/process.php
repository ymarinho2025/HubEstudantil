<?php
$pdo = require __DIR__ . '/../../db.php';
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); $nome = trim($_POST['name'] ?? ''); $senha = $_POST['password'] ?? '';
    if ($email==='' || $nome==='' || $senha==='') $mensagem='Todos os campos são obrigatórios.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $mensagem='Email inválido.';
    elseif (mb_strlen($nome)<2 || mb_strlen($nome)>50) $mensagem='Nome deve ter entre 2 e 50 caracteres.';
    elseif (strlen($senha)<8) $mensagem='A senha deve ter no mínimo 8 caracteres.';
    else {
        try {
            $stmt=$pdo->prepare('INSERT INTO users(name,email,password) VALUES(:n,:e,:p)');
            $stmt->execute([':n'=>$nome, ':e'=>$email, ':p'=>password_hash($senha, PASSWORD_DEFAULT)]);
            header('Location: /login.php?registered=1'); exit;
        } catch (PDOException $e) { $mensagem = str_contains($e->getMessage(), 'unique') ? 'Email já cadastrado.' : 'Erro ao registrar.'; }
    }
}
