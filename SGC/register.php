<?php
require_once __DIR__ . '/config/auth.php';
$pdo = hub_pdo();
$mensagem = '';
$success = false;

if (hub_current_user($pdo)) {
    header('Location: /home/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $mensagem = 'Preencha todos os campos.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 50) {
        $mensagem = 'O nome deve ter entre 2 e 50 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Informe um e-mail válido.';
    } elseif (strlen($password) < 8) {
        $mensagem = 'A senha deve ter no mínimo 8 caracteres.';
    } elseif ($password !== $confirm) {
        $mensagem = 'As senhas não coincidem.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE LOWER(email)=LOWER(:email) LIMIT 1');
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $mensagem = 'Este e-mail já está cadastrado.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (name,email,password,roles)
                 VALUES (:name,:email,:password,1)
                 RETURNING id,name,email,roles'
            );
            $stmt->execute([
                ':name'=>$name, ':email'=>$email, ':password'=>$hash
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            hub_issue_auth_cookie($user);
            hub_record_login($pdo, (int)$user['id']);
            header('Location: /home/');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Solicitar conta | SGC</title>
<link rel="stylesheet" href="./src/css/style.css">
<link rel="stylesheet" href="./src/css/form.css">
<style>
form h2{color:#fff;text-align:center;font-size:18px}
.feedback{background:#fff9;padding:8px;border-radius:4px;color:#7d0000;margin:8px 0}
.back{display:block;text-align:center;color:#fff;margin-top:10px;font-size:12px}
</style>
</head>
<body>
<header><img src="./src/images/dsa.png" alt="Logos dos clubes"></header>
<h1>Sistema de Gestão de Clubes</h1>
<main>
<form method="post">
<h2>Solicitar uma conta</h2>
<label>Nome:</label>
<input name="name" required maxlength="50" placeholder="Seu nome">
<label>E-mail:</label>
<input name="email" type="email" required maxlength="100" placeholder="seu@email.com">
<label>Senha:</label>
<input name="password" type="password" minlength="8" required placeholder="Mínimo 8 caracteres">
<label>Confirmar senha:</label>
<input name="confirm_password" type="password" minlength="8" required placeholder="Repita a senha">
<?php if($mensagem): ?><div class="feedback"><?= htmlspecialchars($mensagem,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
<button type="submit">Criar conta</button>
<a class="back" href="/index.php">Voltar ao login</a>
</form>
</main>
</body>
</html>
