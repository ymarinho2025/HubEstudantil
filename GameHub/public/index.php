<?php if (!empty($_COOKIE['auth_token'])) { header('Location: /home.php'); exit; } ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GameHub</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="marquee-bar">
  <span class="marquee-inner">
    &nbsp;★ GAMEHUB ARCADE &nbsp;★ INSERT COIN &nbsp;★ HIGH SCORES &nbsp;★ 1 PLAYER START &nbsp;★ GAME OVER &nbsp;★ CONTINUE? &nbsp;★ FLAPPY BIRD &nbsp;★ PRESS START &nbsp;★ NEW RECORD &nbsp;★ LEVEL UP &nbsp;★ GAMEHUB ARCADE &nbsp;★ INSERT COIN &nbsp;★ HIGH SCORES &nbsp;★ 1 PLAYER START &nbsp;★ GAME OVER &nbsp;★ CONTINUE? &nbsp;★ FLAPPY BIRD &nbsp;★ PRESS START &nbsp;★ NEW RECORD &nbsp;★ LEVEL UP &nbsp;
  </span>
</div>

<main class="auth-wrap">
  <section class="auth-card" style="text-align:center; max-width:480px;">

    <div class="brand" style="justify-content:center; margin-bottom:32px;">
      <span class="brand-icon">G</span>
      <span class="brand-text">GameHub</span>
    </div>

    <div style="font-family:'Press Start 2P',monospace; font-size:9px; color:var(--muted); letter-spacing:2px; margin-bottom:24px; line-height:2;">
      © 2025 GAMEHUB SYSTEMS<br>
      ALL RIGHTS RESERVED
    </div>

    <h1 style="font-family:'Press Start 2P',monospace; font-size:15px; line-height:1.8; color:var(--brand); text-shadow:0 0 15px rgba(0,245,200,0.5); margin:0 0 10px;">
      HUB DE JOGOS
    </h1>

    <p class="muted" style="margin:0 0 8px; font-size:12px;">
      Entre, jogue e salve sua melhor pontuação no NeonDB PostgreSQL.
    </p>

    <div style="font-family:'Press Start 2P',monospace; font-size:9px; color:var(--brand2); animation:blink 1.2s step-end infinite; margin:24px 0 32px; letter-spacing:2px;">
      ▼ PRESS START ▼
    </div>

    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
      <a class="btn" href="/login.php">▶ Entrar</a>
      <a class="btn secondary" href="/register.php">+ Criar conta</a>
    </div>

    <div style="margin-top:36px; display:flex; justify-content:center; gap:24px; opacity:0.3;">
      <span style="font-family:'Press Start 2P',monospace; font-size:7px; color:var(--brand);">◆</span>
      <span style="font-family:'Press Start 2P',monospace; font-size:7px; color:var(--brand2);">◆</span>
      <span style="font-family:'Press Start 2P',monospace; font-size:7px; color:var(--brand3);">◆</span>
    </div>

  </section>
</main>

</body>
</html>
