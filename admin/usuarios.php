<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('admin');

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        $erro = 'Token invalido.';
    } else {
        $acao = $_POST['acao'] ?? '';

        if ($acao === 'criar') {
            $nome = trim($_POST['nome'] ?? '');
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $senha = $_POST['senha'] ?? '';
            $telefone = trim($_POST['telefone'] ?? '');
            $tipo = $_POST['tipo_usuario'] ?? 'tutor';

            if ($nome && $email && $senha) {
                $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
                $check->execute([$email]);
                if ($check->fetch()) {
                    $erro = 'Email ja cadastrado.';
                } else {
                    $hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, telefone, tipo_usuario) VALUES (?, ?, ?, ?, ?)');
                    if ($stmt->execute([$nome, $email, $hash, $telefone, $tipo])) {
                        $sucesso = 'Usuario criado.';
                    } else {
                        $erro = 'Erro ao criar usuario.';
                    }
                }
            } else {
                $erro = 'Preencha os campos obrigatorios.';
            }
        } elseif ($acao === 'editar') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $telefone = trim($_POST['telefone'] ?? '');
            $tipo = $_POST['tipo_usuario'] ?? 'tutor';
            $senha = $_POST['senha'] ?? '';

            if ($nome && $email) {
                $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
                $check->execute([$email, $id]);
                if ($check->fetch()) {
                    $erro = 'Email ja usado por outro usuario.';
                } else {
                    if ($senha) {
                        $hash = password_hash($senha, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ?, senha = ?, telefone = ?, tipo_usuario = ? WHERE id = ?');
                        $stmt->execute([$nome, $email, $hash, $telefone, $tipo, $id]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ?, telefone = ?, tipo_usuario = ? WHERE id = ?');
                        $stmt->execute([$nome, $email, $telefone, $tipo, $id]);
                    }
                    $sucesso = 'Usuario atualizado.';
                }
            } else {
                $erro = 'Preencha os campos obrigatorios.';
            }
        } elseif ($acao === 'excluir') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id !== (int)$_SESSION['usuario_id']) {
                $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
                $stmt->execute([$id]);
                $sucesso = 'Usuario excluido.';
            } else {
                $erro = 'Nao e possivel excluir o proprio usuario.';
            }
        }
    }
}

$usuarios = $pdo->query('SELECT * FROM usuarios ORDER BY nome')->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Usuarios</h1>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if ($sucesso): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

<div class="card">
    <div class="acoes-topo">
        <h2>Novo Usuario</h2>
    </div>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
        <input type="hidden" name="acao" value="criar">
        <div>
            <label>Nome</label>
            <input type="text" name="nome" required>
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>Senha</label>
            <input type="password" name="senha" required>
        </div>
        <div>
            <label>Telefone</label>
            <input type="text" name="telefone">
        </div>
        <div>
            <label>Tipo</label>
            <select name="tipo_usuario">
                <option value="tutor">Tutor</option>
                <option value="veterinario">Veterinario</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <button type="submit">Criar</button>
    </form>
</div>

<div class="card">
    <h2>Lista de Usuarios</h2>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Tipo</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nome']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['telefone']) ?></td>
                <td><?= htmlspecialchars($u['tipo_usuario']) ?></td>
                <td>
                    <div class="acoes-btn">
                        <button type="button" class="btn-editar btn-sm" onclick="editarUsuario(<?= htmlspecialchars(json_encode($u)) ?>)">Editar</button>
                        <?php if ($u['id'] !== $_SESSION['usuario_id']): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirma a exclusao?')">
                            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn-perigo btn-sm">Excluir</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modalEditar">
    <div class="modal">
        <h2>Editar Usuario</h2>
        <form method="POST" id="formEditar">
            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label>Nome</label>
                <input type="text" name="nome" id="edit_nome" required>
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            <div>
                <label>Nova Senha (deixe vazio para manter)</label>
                <input type="password" name="senha" id="edit_senha">
            </div>
            <div>
                <label>Telefone</label>
                <input type="text" name="telefone" id="edit_telefone">
            </div>
            <div>
                <label>Tipo</label>
                <select name="tipo_usuario" id="edit_tipo">
                    <option value="tutor">Tutor</option>
                    <option value="veterinario">Veterinario</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-secundario" onclick="fecharModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarUsuario(u) {
    document.getElementById('edit_id').value = u.id;
    document.getElementById('edit_nome').value = u.nome;
    document.getElementById('edit_email').value = u.email;
    document.getElementById('edit_telefone').value = u.telefone || '';
    document.getElementById('edit_tipo').value = u.tipo_usuario;
    document.getElementById('edit_senha').value = '';
    document.getElementById('modalEditar').classList.add('ativo');
}
function fecharModal() {
    document.getElementById('modalEditar').classList.remove('ativo');
}
document.getElementById('modalEditar').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>

</main>
</body>
</html>
