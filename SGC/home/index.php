<?php
require_once dirname(__DIR__) . '/config/auth.php';
$pdo = hub_pdo();
$user = hub_require_user('/index.php', $pdo);
$activitiesUrl = rtrim((string)(getenv('ACTIVITIES_URL') ?: 'https://hubestudantil-atividades.vercel.app'), '/') . '/home.php';
$gamehubUrl = rtrim((string)(getenv('GAMEHUB_URL') ?: 'https://hubestudantil-games.vercel.app'), '/') . '/home.php';

if (isset($_GET['logout'])) {
    hub_clear_auth_cookie();
    header('Location: /index.php');
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>HubEstudantil | Portal</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f5f7fb;color:#172033}
header{padding:22px 7%;background:#fff;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center}
header a{color:#172033;text-decoration:none}.user{color:#667085;font-size:14px}
main{max-width:1100px;margin:55px auto;padding:0 24px}.hero{text-align:center;margin-bottom:40px}
h1{font-size:42px;margin-bottom:10px}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.card{background:#fff;border:1px solid #e4e7ec;border-radius:18px;padding:28px;box-shadow:0 8px 24px rgba(16,24,40,.06)}
.card p{min-height:78px;color:#475467;line-height:1.55}.btn{display:inline-block;padding:12px 18px;border-radius:10px;background:#3157d5;color:#fff;text-decoration:none;font-weight:bold}
.note{margin-top:28px;background:#fff;border:1px solid #e4e7ec;border-radius:14px;padding:18px;line-height:1.6}
@media(max-width:800px){.grid{grid-template-columns:1fr}}
</style></head>
<body>
<header>
 <strong>HubEstudantil</strong>
 <div class="user">Olá, <?= htmlspecialchars($user['name']) ?> · <a href="?logout=1">Sair</a></div>
</header>
<main>
<section class="hero">
    <h1>HubEstudantil</h1>
    <p>Escolha uma das opções abaixo para continuar.</p>
</section>
<section class="grid">
<div class="card"><h2>📚 Atividades</h2><p>Envio, acompanhamento e recursos acadêmicos.</p><a class="btn" id="activitiesLink" href="<?= htmlspecialchars($activitiesUrl, ENT_QUOTES, 'UTF-8') ?>">Acessar atividades</a></div>
<div class="card"><h2>🎮 GameHub</h2><p>Aprendizagem por jogos, pontuações e ranking.</p><a class="btn" id="gameLink" href="<?= htmlspecialchars($gamehubUrl, ENT_QUOTES, 'UTF-8') ?>">Aprender jogando</a></div>
</section>
</main>
<script>
(function(){
 const q=new URLSearchParams(location.search);
 if(q.get('atividades')) document.getElementById('activitiesLink').href=q.get('atividades');
 if(q.get('gamehub')) document.getElementById('gameLink').href=q.get('gamehub');
})();
</script>
</body>
</html>
