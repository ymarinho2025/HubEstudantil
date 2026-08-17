<?php
require_once __DIR__ . '/../src/Controllers/admin/isAdmin.php';
require_once __DIR__ . '/../src/Controllers/login/deslogar.php';

// isAdmin.php valida o administrador e carrega $pdo pelo db.php
if (!isset($pdo) || !($pdo instanceof PDO)) {
    header("Location: /login.php");
    exit();
}

// ========================
// MARCAR COMO LIDA
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_lida'])) {
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE suporte SET status = 'lida' WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    header("Location: admin-suporte.php?msg=status_atualizado");
    exit();
}

// ========================
// EXCLUIR MENSAGEM
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_msg'])) {
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM suporte WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    header("Location: admin-suporte.php?msg=mensagem_excluida");
    exit();
}

// ========================
// ESTATÍSTICAS
// ========================
$total_mensagens = (int)$pdo->query("SELECT COUNT(*) FROM suporte")->fetchColumn();
$pendentes = (int)$pdo->query("SELECT COUNT(*) FROM suporte WHERE status = 'pendente'")->fetchColumn();
$lidas = (int)$pdo->query("SELECT COUNT(*) FROM suporte WHERE status = 'lida'")->fetchColumn();

// ========================
// LISTAGEM DAS MENSAGENS
// ========================
$stmt = $pdo->query("
    SELECT
        s.id,
        s.assunto,
        s.mensagem,
        s.status,
        s.created_at,
        u.name,
        u.email
    FROM suporte s
    INNER JOIN users u ON u.id = s.user_id
    ORDER BY s.created_at DESC
");

$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - Suporte</title>
    <link rel="stylesheet" href="/css/admin/base.css">
    <link rel="stylesheet" href="/css/admin/components.css">
    <link rel="stylesheet" href="/css/admin/responsive.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>Painel ADM</h2>
            <a href="/admin.php">Usuários</a>
            <a href="/admin-suporte.php" class="active">Suporte</a>
            <a href="/home.php">Menu inicial</a>

            <form action="admin-suporte.php" method="GET">
                <button type="submit" name="deslogar" value="1">Deslogar</button>
            </form>
        </aside>

        <main class="main">
            <div class="header">
                <h1>Mensagens de Suporte</h1>
                <div class="user">Administrador</div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert-msg">
                    <?php
                        if ($_GET['msg'] === 'status_atualizado') {
                            echo 'Mensagem marcada como lida com sucesso.';
                        } elseif ($_GET['msg'] === 'mensagem_excluida') {
                            echo 'Mensagem excluída com sucesso.';
                        }
                    ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_mensagens ?></div>
                    <div class="stat-label">Total de Mensagens</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= $pendentes ?></div>
                    <div class="stat-label">Pendentes</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= $lidas ?></div>
                    <div class="stat-label">Lidas</div>
                </div>
            </div>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar por nome, email, assunto ou mensagem...">
            </div>

            <div class="card">
                <table id="supportTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Assunto</th>
                            <th>Mensagem</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody id="supportTableBody">
                        <?php if (empty($mensagens)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center;">Nenhuma mensagem encontrada</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mensagens as $msg): ?>
                                <tr
                                    data-search="<?= strtolower(htmlspecialchars(
                                        $msg['name'] . ' ' .
                                        $msg['email'] . ' ' .
                                        $msg['assunto'] . ' ' .
                                        $msg['mensagem'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )) ?>"
                                >
                                    <td><?= (int)$msg['id'] ?></td>
                                    <td><?= htmlspecialchars($msg['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($msg['assunto'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <div class="msg-box"><?= nl2br(htmlspecialchars($msg['mensagem'], ENT_QUOTES, 'UTF-8')) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($msg['status'] === 'pendente'): ?>
                                            <span class="role role-pendente">Pendente</span>
                                        <?php else: ?>
                                            <span class="role role-lida">Lida</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <div class="actions">
                                            <?php if ($msg['status'] === 'pendente'): ?>
                                                <form action="admin-suporte.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                                                    <button type="submit" name="marcar_lida" class="btn btn-edit">
                                                        Marcar lida
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <form action="admin-suporte.php" method="POST" style="display:inline;" onsubmit="return confirm('Deseja excluir esta mensagem?');">
                                                <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                                                <button type="submit" name="delete_msg" class="btn btn-delete">
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll('#supportTableBody tr[data-search]');

        searchInput?.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();

            rows.forEach(row => {
                const content = row.getAttribute('data-search') || '';
                row.style.display = content.includes(termo) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
