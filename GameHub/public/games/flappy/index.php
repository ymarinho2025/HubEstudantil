<?php require_once __DIR__ . '/../../../src/Controllers/login/auth.php'; ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Flappy Bird | GameHub</title>
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=VT323&family=Press+Start+2P&family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="marquee-bar">
  <span class="marquee-inner">
    &nbsp;★ FLAPPY BIRD &nbsp;★ PRESS SPACE TO JUMP &nbsp;★ AVOID THE PIPES &nbsp;★ SAVE YOUR HIGH SCORE &nbsp;★ GOOD LUCK PLAYER &nbsp;★ FLAPPY BIRD &nbsp;★ PRESS SPACE TO JUMP &nbsp;★ AVOID THE PIPES &nbsp;★ SAVE YOUR HIGH SCORE &nbsp;★ GOOD LUCK PLAYER &nbsp;
  </span>
</div>

<header class="topbar">
  <div class="brand">
    <span class="brand-icon">G</span>
    <span class="brand-text">Flappy Bird</span>
  </div>
  <div class="actions">
    <a class="btn secondary" href="/home.php">← Hub</a>
  </div>
</header>

<main class="game-page">

  <!-- Stats row -->
  <div class="game-hints">
    <span>SPACE / CLICK = PULAR</span>
    <span>•</span>
    <span>CLIQUE PARA RECOMEÇAR</span>
  </div>

  <div class="game-frame">
    <canvas id="game-canvas" width="320" height="480"></canvas>
  </div>

</main>

<script src="./jogo.js"></script>

</body>
</html>
