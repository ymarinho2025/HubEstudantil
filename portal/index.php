<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>HubEstudantil</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f5f7fb;color:#172033}
header{padding:24px 7%;background:#fff;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between}
main{max-width:1100px;margin:55px auto;padding:0 24px}.hero{text-align:center;margin-bottom:40px}
h1{font-size:44px;margin-bottom:10px}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.card{background:#fff;border:1px solid #e4e7ec;border-radius:18px;padding:28px;box-shadow:0 8px 24px rgba(16,24,40,.06)}
.card p{min-height:70px;color:#475467;line-height:1.55}.btn{display:inline-block;padding:12px 18px;border-radius:10px;background:#3157d5;color:#fff;text-decoration:none;font-weight:bold}
.note{margin-top:28px;background:#fff;border:1px solid #e4e7ec;border-radius:14px;padding:18px;line-height:1.6}
@media(max-width:800px){.grid{grid-template-columns:1fr}}
</style></head>
<body>
<header><strong>HubEstudantil</strong><span>Portal integrado</span></header>
<main><section class="hero"><h1>HubEstudantil</h1><p>Gestão, atividades e aprendizagem em um único portal.</p></section>
<section class="grid">
<div class="card"><h2>🏠 SGC</h2><p>Área inicial e recursos do SGC.</p><a class="btn" href="http://127.0.0.1:8001/">Acessar</a></div>
<div class="card"><h2>📚 Atividades</h2><p>PHP-web-app com páginas PHP e assets agora reunidos na pasta pública.</p><a class="btn" href="http://127.0.0.1:8002/">Acessar</a></div>
<div class="card"><h2>🎮 GameHub</h2><p>Aprendizagem por jogos, pontuação e ranking.</p><a class="btn" href="http://127.0.0.1:8003/">Acessar</a></div>
</section>
<div class="note"><b>Banco:</b> conexão compartilhada via Neon, com compatibilidade para libpq antigo/SNI.</div>
</main></body></html>