<?php
require_once __DIR__ . '/../src/Controllers/admin/isAdmin.php';
require_once __DIR__ . '/../src/Controllers/login/deslogar.php';
require_once __DIR__ . '/../src/Controllers/admin/painelAdmin.php';
require_once __DIR__ . '/../src/Controllers/admin/delete.php';
require_once __DIR__ . '/../src/Controllers/admin/edit.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="/css/admin/base.css">
    <link rel="stylesheet" href="/css/admin/components.css">
    <link rel="stylesheet" href="/css/admin/responsive.css">
    <script src="/js/admin/admin.js" defer></script>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>Painel ADM</h2>
            <a href="#" class="active">Usuários</a>
            <a href="/admin-suporte.php">Suporte</a>
            <a href="/home.php">Menu inicial</a>

            <form action="admin.php" method="GET">
                <button type="submit" name="deslogar" value="1">Deslogar</button>
            </form>
        </aside>

        <main class="main">
            <div class="header">
                <h1>Painel Administrativo</h1>
                <div class="user">Administrador</div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert-msg">
                    <?php
                        if ($_GET['msg'] === 'role_atualizada') {
                            echo "Role atualizada com sucesso.";
                        } elseif ($_GET['msg'] === 'nao_pode_alterar_proprio_role') {
                            echo "Você não pode alterar sua própria role.";
                        } elseif ($_GET['msg'] === 'id_invalido') {
                            echo "ID inválido.";
                        } elseif ($_GET['msg'] === 'role_invalida') {
                            echo "Role inválida.";
                        } elseif ($_GET['msg'] === 'erro_prepare') {
                            echo "Erro interno ao preparar a operação.";
                        } elseif ($_GET['msg'] === 'erro_update') {
                            echo "Erro ao atualizar a role.";
                        } elseif ($_GET['msg'] === 'deletado') {
                            echo "Usuário excluído com sucesso.";
                        } elseif ($_GET['msg'] === 'erro_delete') {
                            echo "Erro ao excluir usuário.";
                        }
                    ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= (int)$total_usuarios ?></div>
                    <div class="stat-label">Total de Usuários</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= (int)$admins ?></div>
                    <div class="stat-label">Administradores</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= isset($professores) ? (int)$professores : 0 ?></div>
                    <div class="stat-label">Professores</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= (int)$usuarios_comuns ?></div>
                    <div class="stat-label">Usuários Comuns</div>
                </div>
            </div>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar por nome ou email...">
            </div>

            <div class="card">
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody id="userTableBody">
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">Nenhum usuário encontrado</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr
                                    data-name="<?= strtolower(htmlspecialchars($usuario['name'])) ?>"
                                    data-email="<?= strtolower(htmlspecialchars($usuario['email'])) ?>"
                                >
                                    <td><?= (int)$usuario['id'] ?></td>
                                    <td><?= htmlspecialchars($usuario['name']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>

                                    <td>
                                        <?php if ((int)$usuario['roles'] === 3): ?>
                                            <span class="role role-admin">Administrador</span>
                                        <?php elseif ((int)$usuario['roles'] === 2): ?>
                                            <span class="role role-prof">Professor</span>
                                        <?php else: ?>
                                            <span class="role role-user">Usuário</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="actions">
                                            <button
                                                type="button"
                                                class="btn btn-edit"
                                                onclick="abrirModalEditar(
                                                    '<?= (int)$usuario['id'] ?>',
                                                    '<?= htmlspecialchars($usuario['name'], ENT_QUOTES) ?>',
                                                    '<?= (int)$usuario['roles'] ?>'
                                                )"
                                            >
                                                Editar
                                            </button>

                                            <form action="admin.php" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                                <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
                                                <input type="submit" name="delete" class="btn btn-delete" value="Excluir">
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

    <!-- MODAL DE EDIÇÃO -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModalEditar()">&times;</span>
            <h2>Editar Usuário</h2>

            <form action="admin.php" method="POST">
                <input type="hidden" name="edit_id" id="edit_id">

                <div class="form-group">
                    <label for="edit_nome">Usuário</label>
                    <input type="text" id="edit_nome" readonly>
                </div>

                <div class="form-group">
                    <label for="edit_role">Selecionar Role</label>
                    <select name="edit_role" id="edit_role" required>
                        <option value="1">Usuário Normal</option>
                        <option value="2">Professor</option>
                        <option value="3">Administrador</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="fecharModalEditar()">Cancelar</button>
                    <button type="submit" name="update_role" class="btn btn-save">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>