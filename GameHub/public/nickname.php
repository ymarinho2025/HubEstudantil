<?php
$pdo=require __DIR__.'/../src/db.php';
require_once __DIR__.'/../config/auth.php';

$user=gamehub_require_user('/login.php',$pdo);
gamehub_ensure_nickname_schema($pdo);

if (trim((string)($user['nickname'] ?? '')) !== '') {
    header('Location: /home.php');
    exit;
}

$erro='';
$nickname='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nickname=trim((string)($_POST['nickname'] ?? ''));

    if ($nickname==='') {
        $erro='Digite um nickname.';
    } elseif (!gamehub_valid_nickname($nickname)) {
        $erro='Use de 3 a 15 caracteres, somente letras, números ou _. Não use espaços, emojis ou símbolos.';
    } else {
        $check=$pdo->prepare('SELECT id FROM users WHERE LOWER(nickname)=LOWER(:nickname) AND id<>:id LIMIT 1');
        $check->execute([':nickname'=>$nickname, ':id'=>(int)$user['id']]);

        if ($check->fetch()) {
            $erro='Desculpe, esse nickname já existe. Crie outro.';
        } else {
            try {
                $update=$pdo->prepare('UPDATE users SET nickname=:nickname WHERE id=:id AND nickname IS NULL');
                $update->execute([':nickname'=>$nickname, ':id'=>(int)$user['id']]);

                if ($update->rowCount()===1) {
                    header('Location: /home.php');
                    exit;
                }

                // Se outra requisição definiu o nickname simultaneamente, apenas segue para o GameHub.
                $fresh=gamehub_current_user($pdo);
                if ($fresh && trim((string)($fresh['nickname'] ?? ''))!=='') {
                    header('Location: /home.php');
                    exit;
                }
                $erro='Não foi possível salvar o nickname. Tente novamente.';
            } catch (PDOException $e) {
                // PostgreSQL SQLSTATE 23505 = violação de unicidade (corrida entre dois usuários).
                if ($e->getCode()==='23505') {
                    $erro='Desculpe, esse nickname já existe. Crie outro.';
                } else {
                    throw $e;
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Escolher nickname | GameHub</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="marquee-bar"><span class="marquee-inner">★ GAMEHUB &nbsp;★ ESCOLHA SEU NICKNAME &nbsp;★ IDENTIDADE ÚNICA &nbsp;★ RANKING &nbsp;★ GAMEHUB &nbsp;</span></div>
<main class="auth-wrap">
  <section class="auth-card">
    <div class="brand" style="justify-content:center;margin-bottom:28px"><span class="brand-icon">G</span><span class="brand-text">GameHub</span></div>
    <div style="font-family:'Orbitron',monospace;font-size:10px;letter-spacing:3px;text-transform:uppercase;color:var(--muted);margin-bottom:6px">// IDENTIDADE DO JOGADOR</div>
    <h1>Crie seu nickname</h1>
    <p class="muted">Esse será o seu nome público no ranking. Cada conta possui um ID único e somente um nickname.</p>

    <?php if($erro!==''): ?><p class="error">✗ <?=htmlspecialchars($erro)?></p><?php endif; ?>

    <form method="post" autocomplete="off" style="margin-top:20px">
      <label class="field">
        Nickname
        <input name="nickname" value="<?=htmlspecialchars($nickname)?>" minlength="3" maxlength="15" pattern="[A-Za-zÀ-ÖØ-öø-ÿ0-9_]+" required autofocus placeholder="Ex.: Felipe_10">
      </label>
      <p class="muted" style="font-size:11px;line-height:1.6">3 a 15 caracteres • sem espaços • sem emojis • somente letras, números e _ • nickname único</p>
      <button type="submit" style="width:100%;margin-top:20px;padding:14px;font-size:12px;letter-spacing:2px">✓ CRIAR NICKNAME</button>
    </form>
  </section>
</main>
</body>
</html>
