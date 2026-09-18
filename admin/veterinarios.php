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
            $id_usuario = (int)($_POST['id_usuario'] ?? 0);
            $crmv = trim($_POST['crmv'] ?? '');
            $especialidade = trim($_POST['especialidade'] ?? '');

            if ($id_usuario && $crmv) {
                $stmt = $pdo->prepare('INSERT INTO veterinarios (id_usuario, crmv, especialidade) VALUES (?, ?, ?)');
                if ($stmt->execute([$id_usuario, $crmv, $especialidade])) {
                    $sucesso = 'Veterinario cadastrado.';
                } else {
                    $erro = 'Erro ao cadastrar.';
                }
            } else {
                $erro = 'Preencha os campos obrigatorios.';
            }
        } elseif ($acao === 'editar') {
            $id = (int)($_POST['id'] ?? 0);
            $crmv = trim($_POST['crmv'] ?? '');
            $especialidade = trim($_POST['especialidade'] ?? '');

            if ($crmv) {
                $stmt = $pdo->prepare('UPDATE veterinarios SET crmv = ?, especialidade = ? WHERE id = ?');
                $stmt->execute([$crmv, $especialidade, $id]);
                $sucesso = 'Veterinario atualizado.';
            } else {
                $erro = 'CRMV obrigatorio.';
            }
        } elseif ($acao === 'excluir') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM veterinarios WHERE id = ?');
            $stmt->execute([$id]);
            $sucesso = 'Veterinario removido.';
        }
    }
}

$stmt = $pdo->query('SELECT u.id, u.nome FROM usuarios u WHERE u.tipo_usuario = "veterinario" AND u.id NOT IN (SELECT id_usuario FROM veterinarios) ORDER BY u.nome');
$usuarios_vet = $stmt->fetchAll();

$vets = $pdo->query('SELECT v.*, u.nome FROM veterinarios v JOIN usuarios u ON v.id_usuario = u.id ORDER BY u.nome')->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Veterinarios</h1>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if ($sucesso): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

<div class="card">
    <h2>Cadastrar Veterinario</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
        <input type="hidden" name="acao" value="criar">
        <div>
            <label>Usuario</label>
            <select name="id_usuario" required>
                <option value="">Selecione</option>
                <?php foreach ($usuarios_vet as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>CRMV</label>
            <input type="text" name="crmv" required>
        </div>
        <div>
            <label>Especialidade</label>
            <input type="text" name="especialidade">
        </div>
        <button type="submit">Cadastrar</button>
    </form>
</div>

<div class="card">
    <h2>Veterinarios Cadastrados</h2>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>CRMV</th>
                <th>Especialidade</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vets as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['nome']) ?></td>
                <td><?= htmlspecialchars($v['crmv']) ?></td>
                <td><?= htmlspecialchars($v['especialidade']) ?></td>
                <td>
                    <div class="acoes-btn">
                        <button type="button" class="btn-editar btn-sm" onclick="editarVet(<?= htmlspecialchars(json_encode($v)) ?>)">Editar</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirma a exclusao?')">
                            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= $v['id'] ?>">
                            <button type="submit" class="btn-perigo btn-sm">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modalEditar">
    <div class="modal">
        <h2>Editar Veterinario</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label>CRMV</label>
                <input type="text" name="crmv" id="edit_crmv" required>
            </div>
            <div>
                <label>Especialidade</label>
                <input type="text" name="especialidade" id="edit_especialidade">
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-secundario" onclick="fecharModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarVet(v) {
    document.getElementById('edit_id').value = v.id;
    document.getElementById('edit_crmv').value = v.crmv;
    document.getElementById('edit_especialidade').value = v.especialidade || '';
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
