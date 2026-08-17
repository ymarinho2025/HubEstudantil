<?php require_once __DIR__ . '/../src/Controllers/login/storeLogin.php'; ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login | GameHub</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="marquee-bar">
  <span class="marquee-inner">
    &nbsp;★ GAMEHUB ARCADE &nbsp;★ IDENTIFY PLAYER &nbsp;★ ENTER CREDENTIALS &nbsp;★ ACCESS GRANTED &nbsp;★ WELCOME BACK &nbsp;★ HIGH SCORE AWAITS &nbsp;★ GAMEHUB ARCADE &nbsp;★ IDENTIFY PLAYER &nbsp;★ ENTER CREDENTIALS &nbsp;★ ACCESS GRANTED &nbsp;★ WELCOME BACK &nbsp;★ HIGH SCORE AWAITS &nbsp;
  </span>
</div>

<main class="auth-wrap">
  <section class="auth-card">

    <div class="brand" style="justify-content:center; margin-bottom:28px;">
      <span class="brand-icon">G</span>
      <span class="brand-text">GameHub</span>
    </div>

    <div style="font-family:'Orbitron',monospace; font-size:10px; letter-spacing:3px; text-transform:uppercase; color:var(--muted); margin-bottom:6px;">
      // IDENTIFICAÇÃO DE JOGADOR
    </div>
    <h1>Login</h1>

    <?php if(isset($_GET['registered'])): ?>
      <p class="success">✓ Conta criada com sucesso. Faça login.</p>
    <?php endif; ?>

    <?php if(!empty($loginErro)): ?>
      <p class="error">✗ <?= htmlspecialchars($loginErro) ?></p>
    <?php endif; ?>

    <form method="post" style="margin-top:20px;">
      <label class="field">
        Email
        <input name="email" type="email" required placeholder="jogador@email.com">
      </label>
      <label class="field">
        Senha
        <input name="password" type="password" required placeholder="••••••••">
      </label>
      <button type="submit" style="width:100%; margin-top:20px; padding:14px; font-size:12px; letter-spacing:3px;">
        ▶ ENTRAR
      </button>
    </form>

    <p class="muted" style="text-align:center; margin-top:24px; font-size:12px;">
      Sem conta? <a href="/register.php" style="color:var(--brand2);">Registre-se</a>
    </p>

  </section>
</main>

</body>
</html>
