<?php
require_once dirname(__DIR__, 2) . '/config/auth.php';
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
<section class="hero"><h1>Portal HubEstudantil</h1><p>O mesmo login dá acesso aos módulos integrados.</p></section>
<section class="grid">
<div class="card"><h2>🏠 SGC</h2><p>Recursos e páginas do Sistema de Gestão de Clubes.</p><a class="btn" href="../agenda/">Acessar SGC</a></div>
<div class="card"><h2>📚 Atividades</h2><p>Envio, acompanhamento e recursos acadêmicos do PHP-web-app.</p><a class="btn" id="activitiesLink" href="http://127.0.0.1:8002/home.php">Acessar atividades</a></div>
<div class="card"><h2>🎮 GameHub</h2><p>Aprendizagem por jogos, pontuações e ranking usando a mesma conta.</p><a class="btn" id="gameLink" href="http://127.0.0.1:8003/home.php">Aprender jogando</a></div>
</section>
<div class="note"><b>Login único:</b> o cookie <code>auth_token</code> é usado pelos três módulos no mesmo host.</div>
</main>
<script>
(function(){
 const h=location.hostname;
 if(h==='127.0.0.1'||h==='localhost'){
   document.getElementById('activitiesLink').href='http://'+h+':8002/home.php';
   document.getElementById('gameLink').href='http://'+h+':8003/home.php';
 }
 const q=new URLSearchParams(location.search);
 if(q.get('atividades')) document.getElementById('activitiesLink').href=q.get('atividades');
 if(q.get('gamehub')) document.getElementById('gameLink').href=q.get('gamehub');
})();
</script>
</body>
</html>
