<?php
require_once __DIR__ . '/../src/Controllers/login/auth.php';
require_once __DIR__ . '/../src/Controllers/login/deslogar.php';

// auth.php já garante $userId e carrega $pdo pelo db.php
if (!isset($userId) || !isset($pdo) || !($pdo instanceof PDO)) {
    header("Location: /login.php");
    exit();
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($assunto === '' || $mensagem === '') {
        $msg = "Preencha todos os campos.";
    } elseif (mb_strlen($assunto) > 255) {
        $msg = "O assunto é muito longo.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO suporte (user_id, assunto, mensagem)
                VALUES (:user_id, :assunto, :mensagem)
            ");

            $stmt->execute([
                ':user_id' => (int)$userId,
                ':assunto' => $assunto,
                ':mensagem' => $mensagem,
            ]);

            $msg = "Mensagem enviada com sucesso!";
        } catch (PDOException $e) {
            $msg = "Erro ao enviar mensagem.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Suporte</title>
  <link rel="stylesheet" href="/css/estilo.css">
</head>
<body class="light suporte-page">

<header>
  <div class="brand">
    <span class="brand-icon">E</span>
    <span class="brand-text">Grade</span>
  </div>

  <div class="header-actions">
    <a href="/home.php"><button type="button">Voltar</button></a>

    <form method="GET" style="display:inline;">
      <button type="submit" name="deslogar" value="1">Sair</button>
    </form>

    <button id="toggleTema" type="button">🌙</button>
  </div>
</header>

<div class="container">
  <div class="box suporte-box">
    <h2>Suporte</h2>

    <form method="POST">
      <input type="text" name="assunto" placeholder="Assunto" required maxlength="255">

      <textarea name="mensagem" rows="5" placeholder="Mensagem" required></textarea>

      <button class="btn-suporte" type="submit">Enviar</button>
    </form>

    <?php if ($msg): ?>
      <p><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
  </div>
</div>

<script src="/js/app.js"></script>
</body>
</html>
