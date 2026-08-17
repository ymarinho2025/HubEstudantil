<?php
$pdo = require __DIR__ . '/../../db.php';
require_once dirname(__DIR__, 4) . '/config/auth.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email=trim($_POST['email'] ?? '');
    $nome=trim($_POST['name'] ?? '');
    $senha=$_POST['password'] ?? '';

    if ($email==='' || $nome==='' || $senha==='') $mensagem='Todos os campos são obrigatórios.';
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) $mensagem='Email inválido.';
    elseif (mb_strlen($nome)<2 || mb_strlen($nome)>50) $mensagem='Nome deve ter entre 2 e 50 caracteres.';
    elseif (strlen($senha)<8) $mensagem='A senha deve ter no mínimo 8 caracteres.';
    else {
        $stmt=$pdo->prepare('SELECT id FROM users WHERE LOWER(email)=LOWER(:email) LIMIT 1');
        $stmt->execute([':email'=>$email]);

        if ($stmt->fetch()) {
            $mensagem='Email já cadastrado.';
        } else {
            $stmt=$pdo->prepare(
                'INSERT INTO users(name,email,password,roles) VALUES(:n,:e,:p,1) RETURNING id,name,email,roles'
            );
            $stmt->execute([
                ':n'=>$nome,
                ':e'=>$email,
                ':p'=>password_hash($senha,PASSWORD_DEFAULT)
            ]);
            $user=$stmt->fetch(PDO::FETCH_ASSOC);
            hub_issue_auth_cookie($user);
            hub_record_login($pdo,(int)$user['id']);
            header('Location: /home.php');
            exit;
        }
    }
}
