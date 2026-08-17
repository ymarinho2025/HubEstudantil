<?php
require_once __DIR__ . '/../src/Controllers/login/auth.php';
require_once __DIR__ . '/../src/Controllers/image/base64.php';
require_once __DIR__ . '/../src/Controllers/image/storeImage.php';
require_once __DIR__ . '/../src/Controllers/image/getImage.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/form.css">
    <script src="/js/script.js" defer></script>
</head>
<body>
    <button class="btn-menu" id="btn-menu">☰ Menu</button>
    <ul class="menu" id="menu">
        <li><a href="/home.php">INICIO</a></li>
        <li><a href="/fotos.php">FOTOS</a></li>
        <?php 
        if (isset($_COOKIE['auth_token'])) {
            try {
                $decoded = JWT::decode($_COOKIE['auth_token'], new Key($key, 'HS256'));
                $roles = $decoded->roles;
                if ($roles == 3) {
                    echo '<li><a href="/admin.php">ADMIN</a></li>';
                    }
                } catch (Exception $e) {
            } 
        }
        ?>
    </ul>

    <form action="fotos.php" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Envie seu cartão resposta</legend>
            <label for="namefotos">Nome da foto:</label>
            <input type="text" name="namefotos" placeholder="Nome" autofocus>
            <label>Data da foto:</label>
            <input type="number" name="dia" placeholder="Dia" min="1" max="31" required>
            <input type="number" name="mes" placeholder="Mês" min="1" max="12" required>
            <input type="number" name="ano" placeholder="Ano" min="2000" max="2060" required>
            <input type="hidden" name="upload" value="1">
            <input type="file" name="fotos" accept="image/*">
            <input type="submit" value="Enviar">
        </fieldset>
    </form>
    <?php if (!empty($imagens)): ?>
        <h2>Imagens encontradas na mesma data</h2>

        <?php foreach ($imagens as $img): ?>
            <div style="margin-bottom: 20px;">
                <p><strong><?= htmlspecialchars($img['nome']) ?></strong></p>
                <img src="<?= htmlspecialchars($img['imagem']) ?>" alt="Imagem" width="300">
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form action="login.php" method="GET">
        <button class="btn-deslogar" type="submit" name="deslogar" value="1">Deslogar</button>
    </form>
</body>
</html>