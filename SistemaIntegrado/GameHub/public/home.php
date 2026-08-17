<?php
require_once __DIR__ . '/../src/Controllers/login/deslogar.php';
require_once __DIR__ . '/../src/Controllers/login/auth.php';
$games = $pdo->query("SELECT g.*, COALESCE(s.best_score,0) best_score, COALESCE(s.last_score,0) last_score FROM games g LEFT JOIN game_scores s ON s.game_id=g.id AND s.user_id=".(int)$authUser['id']." WHERE g.active=TRUE ORDER BY g.id")->fetchAll();
$ranking = $pdo->query("SELECT u.name, gs.best_score FROM game_scores gs JOIN users u ON u.id=gs.user_id JOIN games g ON g.id=gs.game_id WHERE g.slug='flappy-bird' ORDER BY gs.best_score DESC, gs.updated_at ASC LIMIT 10")->fetchAll();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GameHub</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<!-- Marquee bar -->
<div class="marquee-bar">
  <span class="marquee-inner">
    &nbsp;★ GAMEHUB ARCADE &nbsp;★ INSERT COIN &nbsp;★ HIGH SCORES &nbsp;★ 1 PLAYER START &nbsp;★ GAME OVER &nbsp;★ CONTINUE? &nbsp;★ FLAPPY BIRD &nbsp;★ PRESS START &nbsp;★ NEW RECORD &nbsp;★ LEVEL UP &nbsp;★ GAMEHUB ARCADE &nbsp;★ INSERT COIN &nbsp;★ HIGH SCORES &nbsp;★ 1 PLAYER START &nbsp;★ GAME OVER &nbsp;★ CONTINUE? &nbsp;★ FLAPPY BIRD &nbsp;★ PRESS START &nbsp;★ NEW RECORD &nbsp;★ LEVEL UP &nbsp;
  </span>
</div>

<header class="topbar">
  <div class="brand">
    <span class="brand-icon">G</span>
    <span class="brand-text">GameHub</span>
  </div>
  <div class="actions">
    <span><?= htmlspecialchars($userName) ?></span>
    <a class="btn secondary" href="?deslogar=1">Sair</a>
  </div>
</header>

<main class="container">

  <!-- Hero -->
  <section class="hero">
    <div class="panel">
      <div style="font-family:'Orbitron',monospace; font-size:9px; letter-spacing:3px; color:var(--muted); margin-bottom:12px; text-transform:uppercase;">
        // Sistema Online — NeonDB PostgreSQL
      </div>
      <h1>GAME<br>HUB</h1>
      <p class="muted">O Flappy Bird é o primeiro jogo conectado ao login. Sua melhor pontuação fica salva por usuário no PostgreSQL/NeonDB.</p>
      <div class="status-pills">
        <span class="pill online">● Sistema online</span>
        <span class="pill scorepill">◆ Scores salvos</span>
      </div>
    </div>

    <div class="panel score-box" style="padding:20px;">
      <div class="score">
        <span>Jogos</span>
        <strong><?= count($games) ?></strong>
      </div>
      <div class="score" style="border-top-color:var(--brand2);">
        <span style="color:var(--muted);">Melhor Flappy</span>
        <strong style="color:var(--brand2); text-shadow:0 0 10px var(--brand2);"><?= (int)($games[0]['best_score'] ?? 0) ?></strong>
      </div>
    </div>
  </section>

  <!-- Games list -->
  <h2>Jogos disponíveis</h2>

  <div class="grid">
    <?php foreach($games as $game): ?>
    <article class="card game-card">
      <div class="led-bar"></div>
      <div class="game-cover">
        <img src="<?= htmlspecialchars($game['cover_url']) ?>" alt="">
      </div>
      <div class="game-body">
        <h3><?= htmlspecialchars($game['title']) ?></h3>
        <p class="muted"><?= htmlspecialchars($game['description']) ?></p>
        <p>BEST: <strong><?= (int)$game['best_score'] ?></strong> &nbsp;|&nbsp; LAST: <strong><?= (int)$game['last_score'] ?></strong></p>
        <a class="btn" href="<?= htmlspecialchars($game['game_url']) ?>">▶ Jogar</a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <!-- Ranking -->
  <h2>Ranking — Flappy Bird</h2>

  <div class="panel" style="padding:0; overflow:hidden;">
    <table class="rank-table">
      <thead>
        <tr>
          <th style="width:60px;">#</th>
          <th>Jogador</th>
          <th style="text-align:right; padding-right:20px;">Melhor pontuação</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($ranking as $i => $r): ?>
        <tr>
          <td><?= ['🥇','🥈','🥉'][$i] ?? ($i+1) ?></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td style="text-align:right; padding-right:20px;"><?= (int)$r['best_score'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</main>

</body>
</html>
