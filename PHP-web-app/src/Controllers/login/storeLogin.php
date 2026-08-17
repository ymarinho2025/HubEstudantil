<?php
$pdo = require __DIR__ . '/../db.php';
require_once dirname(__DIR__, 4) . '/config/auth.php';

$loginErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id,name,email,password,roles FROM users WHERE LOWER(email)=LOWER(:email) LIMIT 1'
    );
    $stmt->execute([':email'=>$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && hub_verify_password_and_upgrade($pdo, $user, $senha)) {
        hub_record_login($pdo, (int)$user['id']);
        hub_issue_auth_cookie($user);
        header('Location: /home.php');
        exit;
    }

    $loginErro = 'Email ou senha incorretos.';
} else {
    if (hub_current_user($pdo)) {
        header('Location: /home.php');
        exit;
    }
}
