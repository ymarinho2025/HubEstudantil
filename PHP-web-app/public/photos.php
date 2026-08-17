<?php
require_once __DIR__ . '/../src/Controllers/login/auth.php';
require_once __DIR__ . '/../src/Controllers/login/key.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
|--------------------------------------------------------------------------
| Captura mensagens do backend antigo
|--------------------------------------------------------------------------
*/
ob_start();
require_once __DIR__ . '/../src/Controllers/image/base64.php';
require_once __DIR__ . '/../src/Controllers/image/storeImage.php';
require_once __DIR__ . '/../src/Controllers/image/getImage.php';
$mensagemBackend = trim(ob_get_clean());

function selected($valorAtual, $valorEsperado) {
    return ((string)$valorAtual === (string)$valorEsperado) ? 'selected' : '';
}

$diaSelecionado = $_POST['dia'] ?? date('d');
$mesSelecionado = $_POST['mes'] ?? date('m');
$anoSelecionado = $_POST['ano'] ?? date('Y');

$tipoMensagem = 'erro';
if ($mensagemBackend !== '') {
    $mensagemLower = mb_strtolower($mensagemBackend);
    if (
        str_contains($mensagemLower, 'sucesso') ||
        str_contains($mensagemLower, 'salva')
    ) {
        $tipoMensagem = 'sucesso';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página de Fotos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/photos.css">
    <script src="/js/photos.js" defer></script>
</head>
<body class="light">

<header>
    <div class="brand">
        <span class="brand-icon">E</span>
        <span class="brand-text">Grade</span>
    </div>
</header>

<button id="toggleTema" type="button">Modo Escuro</button>

<div
    id="alerta"
    class="alerta <?= htmlspecialchars($tipoMensagem) ?>"
    data-msg="<?= htmlspecialchars($mensagemBackend) ?>"
></div>

<button class="btn-menu" id="btn-menu" type="button">☰ Menu</button>

<ul class="menu" id="menu">
    <li><a href="/home.php">INÍCIO</a></li>
    <li><a href="/photos.php">FOTOS</a></li>
    <?php
    if (isset($_COOKIE['auth_token'])) {
        try {
            $decoded = JWT::decode($_COOKIE['auth_token'], new Key($key, 'HS256'));
            $roles = $decoded->roles ?? null;

            if ((int)$roles === 3) {
                echo '<li><a href="/admin.php">ADMIN</a></li>';
            }
        } catch (Exception $e) {
        }
    }
    ?>
</ul>

<div class="container">

    <div class="box">
        <h2>Enviar Foto</h2>

        <form action="/photos.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <label for="namefotos">Nome da foto</label>
            <input
                type="text"
                name="namefotos"
                id="namefotos"
                placeholder="Digite um nome para a foto"
                required
            >

            <label class="custom-file-upload">
                <input
                    id="fileInput"
                    type="file"
                    name="fotos"
                    accept="image/*"
                    required
                >
                Escolher imagem
            </label>

            <br><br>

            <img id="preview" style="width:200px; display:none; border-radius:10px;" alt="Pré-visualização">

            <!-- <br><br>

            <label for="diaUpload">Dia</label>
            <select id="diaUpload" name="dia" required>
                <?php for ($i = 1; $i <= 31; $i++): ?>
                    <option value="<?= $i ?>" <?= selected($diaSelecionado, $i) ?>>
                        <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
                -->

            <label for="mesUpload">Mês</label>
            <select id="mesUpload" name="mes" required>
                <option value="1" <?= selected($mesSelecionado, 1) ?>>Janeiro</option>
                <option value="2" <?= selected($mesSelecionado, 2) ?>>Fevereiro</option>
                <option value="3" <?= selected($mesSelecionado, 3) ?>>Março</option>
                <option value="4" <?= selected($mesSelecionado, 4) ?>>Abril</option>
                <option value="5" <?= selected($mesSelecionado, 5) ?>>Maio</option>
                <option value="6" <?= selected($mesSelecionado, 6) ?>>Junho</option>
                <option value="7" <?= selected($mesSelecionado, 7) ?>>Julho</option>
                <option value="8" <?= selected($mesSelecionado, 8) ?>>Agosto</option>
                <option value="9" <?= selected($mesSelecionado, 9) ?>>Setembro</option>
                <option value="10" <?= selected($mesSelecionado, 10) ?>>Outubro</option>
                <option value="11" <?= selected($mesSelecionado, 11) ?>>Novembro</option>
                <option value="12" <?= selected($mesSelecionado, 12) ?>>Dezembro</option>
            </select>

            <label for="anoUpload">Ano</label>
            <select id="anoUpload" name="ano" required>
                <?php for ($i = 2000; $i <= 2060; $i++): ?>
                    <option value="<?= $i ?>" <?= selected($anoSelecionado, $i) ?>>
                        <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>

            <input type="hidden" name="upload" value="1">

            <button type="submit">ENVIAR</button>
        </form>
    </div>

    <div class="box">
        <h2>Filtrar Fotos</h2>

        <form action="/photos.php" method="POST" id="filtroForm">
            <!-- <label for="diaFiltro">Dia</label>
            <select id="diaFiltro" name="dia" required>
                <?php for ($i = 1; $i <= 31; $i++): ?>
                    <option value="<?= $i ?>" <?= selected($diaSelecionado, $i) ?>>
                        <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
                -->
            <label for="mesFiltro">Mês</label>
            <select id="mesFiltro" name="mes" required>
                <option value="1" <?= selected($mesSelecionado, 1) ?>>Janeiro</option>
                <option value="2" <?= selected($mesSelecionado, 2) ?>>Fevereiro</option>
                <option value="3" <?= selected($mesSelecionado, 3) ?>>Março</option>
                <option value="4" <?= selected($mesSelecionado, 4) ?>>Abril</option>
                <option value="5" <?= selected($mesSelecionado, 5) ?>>Maio</option>
                <option value="6" <?= selected($mesSelecionado, 6) ?>>Junho</option>
                <option value="7" <?= selected($mesSelecionado, 7) ?>>Julho</option>
                <option value="8" <?= selected($mesSelecionado, 8) ?>>Agosto</option>
                <option value="9" <?= selected($mesSelecionado, 9) ?>>Setembro</option>
                <option value="10" <?= selected($mesSelecionado, 10) ?>>Outubro</option>
                <option value="11" <?= selected($mesSelecionado, 11) ?>>Novembro</option>
                <option value="12" <?= selected($mesSelecionado, 12) ?>>Dezembro</option>
            </select>

            <label for="anoFiltro">Ano</label>
            <select id="anoFiltro" name="ano" required>
                <?php for ($i = 2000; $i <= 2060; $i++): ?>
                    <option value="<?= $i ?>" <?= selected($anoSelecionado, $i) ?>>
                        <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>

            <button type="submit">Pesquisar</button>
        </form>
    </div>

    <div class="box">
        <h2>Minhas Fotos</h2>
        <div id="galeria" class="fotos">
            <?php if (!empty($imagens)): ?>
                <?php foreach ($imagens as $img): ?>
                    <div class="foto-item">
                        <img
                            src="<?= htmlspecialchars($img['imagem']) ?>"
                            alt="<?= htmlspecialchars($img['nome']) ?>"
                        >
                        <p class="foto-nome"><?= htmlspecialchars($img['nome']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $mensagemBackend === ''): ?>
                <p>Nenhuma foto encontrada para a data informada.</p>
            <?php else: ?>
                <p>Selecione a data e pesquise suas fotos.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<form action="/login.php" method="GET" class="logout-area">
    <button class="btn-deslogar" type="submit" name="deslogar" value="1">Deslogar</button>
</form>

</body>
</html>