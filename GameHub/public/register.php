<?php require_once __DIR__ . '/../src/Controllers/login/process.php'; ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registro | GameHub</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="marquee-bar">
  <span class="marquee-inner">
    &nbsp;★ GAMEHUB ARCADE &nbsp;★ NEW PLAYER REGISTRATION &nbsp;★ CHOOSE YOUR NAME &nbsp;★ JOIN THE LEADERBOARD &nbsp;★ CLAIM YOUR GLORY &nbsp;★ GAMEHUB ARCADE &nbsp;★ NEW PLAYER REGISTRATION &nbsp;★ CHOOSE YOUR NAME &nbsp;★ JOIN THE LEADERBOARD &nbsp;★ CLAIM YOUR GLORY &nbsp;
  </span>
</div>

<main class="auth-wrap">
  <section class="auth-card">

    <div class="brand" style="justify-content:center; margin-bottom:28px;">
      <span class="brand-icon">G</span>
      <span class="brand-text">GameHub</span>
    </div>

    <div style="font-family:'Orbitron',monospace; font-size:10px; letter-spacing:3px; text-transform:uppercase; color:var(--muted); margin-bottom:6px;">
      // NOVO JOGADOR
    </div>
    <h1>Criar conta</h1>

    <?php if(!empty($mensagem)): ?>
      <p class="error">✗ <?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form method="post" style="margin-top:20px;">
      <label class="field">
        Nome completo
        <input name="name" required maxlength="50" placeholder="Seu nome">
      </label>
      <label class="field">
        Email
        <input name="email" type="email" required placeholder="jogador@email.com">
      </label>
      <label class="field">
        Senha
        <input name="password" type="password" minlength="8" required placeholder="Mín. 8 caracteres">
      </label>
      <button type="submit" style="width:100%; margin-top:20px; padding:14px; font-size:12px; letter-spacing:3px;">
        + REGISTRAR
      </button>
    </form>

    <p class="muted" style="text-align:center; margin-top:24px; font-size:12px;">
      Já tem conta? <a href="/login.php" style="color:var(--brand);">Entrar</a>
    </p>

  </section>
</main>

</body>
</html>
