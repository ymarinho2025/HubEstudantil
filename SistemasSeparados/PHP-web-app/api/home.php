<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/login/auth.php';
require_once __DIR__ . '/../src/Controllers/login/deslogar.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$role = null;
$authToken = $_COOKIE['auth_token'] ?? null;

if ($authToken) {
    try {
        $decoded = JWT::decode($authToken, new Key($key, 'HS256'));
        $role = $decoded->roles ?? null;
    } catch (Exception $e) {
        setcookie('auth_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => false,
            'samesite' => 'Lax'
        ]);

        unset($_COOKIE['auth_token']);
        header('Location: /login.php');
        exit();
    }
}

$saudacao = 'Olá';
if (!empty($userName)) {
    $saudacao .= ' ' . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/css/estilo.css">
    <style>
        .logout-form { margin: 0; }
        .admin-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 14px;
            background: rgba(255,255,255,.08);
        }
    </style>
</head>
<body class="light">

<header>
    <div class="brand">
        <span class="brand-icon">E</span>
        <span class="brand-text">Grade</span>
    </div>

    <div class="header-actions">
        <form class="logout-form" action="home.php" method="GET">
            <button type="submit" name="deslogar" value="1">Sair</button>
        </form>
        <button type="button" id="toggleTema">🌙</button>
    </div>
</header>

<div class="container">
    <div class="box">
        <h2 id="saudacao"><?php echo $saudacao; ?></h2>

        <?php if ($role == 3): ?>
            <div class="admin-badge">Perfil administrador ativo</div>
        <?php endif; ?>

        <div class="menu">
            <a href="photos.php">📸 Fotos</a>
            <a href="suporte.php">🛠 Suporte</a>
            <?php if ($role == 3): ?>
                <a href="admin.php">⚙️ Admin</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function () {
    const body = document.body;
    const botaoTema = document.getElementById('toggleTema');
    const temaSalvo = localStorage.getItem('tema');

    if (temaSalvo === 'dark') {
        body.classList.remove('light');
        body.classList.add('dark');
        botaoTema.textContent = '☀️';
    }

    botaoTema.addEventListener('click', function () {
        const modoEscuroAtivo = body.classList.toggle('dark');
        body.classList.toggle('light', !modoEscuroAtivo);
        localStorage.setItem('tema', modoEscuroAtivo ? 'dark' : 'light');
        botaoTema.textContent = modoEscuroAtivo ? '☀️' : '🌙';
    });
})();
</script>
<script src="/js/app.js"></script>
</body>
</html>
