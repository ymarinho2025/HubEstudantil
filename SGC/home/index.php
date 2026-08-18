<?php
require_once dirname(__DIR__) . '/config/auth.php';

$pdo = hub_pdo();
$user = hub_require_user('/index.php', $pdo);

if (isset($_GET['logout'])) {
    hub_clear_auth_cookie();
    header('Location: /index.php');
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>HubEstudantil | Portal</title>

<style>
*{
    box-sizing:border-box
}

body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#f5f7fb;
    color:#172033
}

header{
    padding:22px 7%;
    background:#fff;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    justify-content:space-between;
    align-items:center
}

header a{
    color:#172033;
    text-decoration:none
}

.user{
    color:#667085;
    font-size:14px
}

main{
    max-width:900px;
    margin:55px auto;
    padding:0 24px
}

.hero{
    text-align:center;
    margin-bottom:40px
}

h1{
    font-size:42px;
    margin-bottom:10px
}

.grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:22px
}

.card{
    background:#fff;
    border:1px solid #e4e7ec;
    border-radius:18px;
    padding:28px;
    box-shadow:0 8px 24px rgba(16,24,40,.06)
}

.card p{
    min-height:78px;
    color:#475467;
    line-height:1.55
}

.btn{
    display:inline-block;
    padding:12px 18px;
    border-radius:10px;
    background:#3157d5;
    color:#fff;
    text-decoration:none;
    font-weight:bold
}

@media(max-width:800px){
    .grid{
        grid-template-columns:1fr
    }
}
</style>
</head>

<body>

<header>
    <strong>HubEstudantil</strong>

    <div class="user">
        Olá, <?= htmlspecialchars($user['name'] ?? 'Usuário') ?>
        ·
        <a href="?logout=1">Sair</a>
    </div>
</header>

<main>

<section class="hero">
    <h1>HubEstudantil</h1>
    <p>Escolha uma das opções abaixo para continuar.</p>
</section>

<section class="grid">

    <div class="card">
        <h2>📚 Atividades</h2>

        <p>
            Envio, acompanhamento e recursos acadêmicos.
        </p>

        <a class="btn"
           href="/sso.php?target=atividades">
            Acessar atividades
        </a>
    </div>

    <div class="card">
        <h2>🎮 GameHub</h2>

        <p>
            Aprendizagem por jogos, pontuações e ranking.
        </p>

        <a class="btn"
           href="/sso.php?target=games">
            Aprender jogando
        </a>
    </div>

</section>

</main>

</body>
</html>