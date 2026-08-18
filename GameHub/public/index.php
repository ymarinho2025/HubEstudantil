<?php
$pdo=require __DIR__.'/../src/db.php';
require_once __DIR__.'/../config/auth.php';
if(gamehub_current_user($pdo)){header('Location: /home.php');exit;}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GameHub</title><link rel="stylesheet" href="/css/style.css"></head><body>
<div class="marquee-bar"><span class="marquee-inner">&nbsp;★ GAMEHUB ARCADE &nbsp;★ PRESS START &nbsp;★ APRENDER JOGANDO &nbsp;★ HIGH SCORES &nbsp;★ GAMEHUB ARCADE &nbsp;</span></div>
<main class="auth-wrap"><section class="auth-card" style="text-align:center;max-width:480px"><div class="brand" style="justify-content:center;margin-bottom:32px"><span class="brand-icon">G</span><span class="brand-text">GameHub</span></div>
<div style="font-family:'Press Start 2P',monospace;font-size:9px;color:var(--muted);letter-spacing:2px;margin-bottom:24px;line-height:2">HUB EDUCACIONAL DE JOGOS</div>
<h1 style="font-family:'Press Start 2P',monospace;font-size:15px;line-height:1.8;color:var(--brand)">APRENDER JOGANDO</h1>
<p class="muted">Entre com a mesma conta do HubEstudantil para acessar todos os jogos.</p>
<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:30px"><a class="btn" href="/login.php">▶ Entrar</a><a class="btn secondary" href="/register.php">+ Criar conta</a></div>
</section></main></body></html>