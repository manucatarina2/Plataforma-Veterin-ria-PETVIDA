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
            $nome = trim($_POST['nome_servico'] ?? '');
            $duracao = (int)($_POST['duracao'] ?? 0);
            $valor = (float)($_POST['valor'] ?? 0);

            if ($nome && $duracao > 0) {
                $stmt = $pdo->prepare('INSERT INTO servicos (nome_servico, duracao_minutos, valor) VALUES (?, ?, ?)');
                if ($stmt->execute([$nome, $duracao, $valor])) {
                    $sucesso = 'Servico cadastrado.';
                } else {
                    $erro = 'Erro ao cadastrar.';
                }
            } else {
                $erro = 'Preencha os campos obrigatorios.';
            }
        } elseif ($acao === 'editar') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome_servico'] ?? '');
            $duracao = (int)($_POST['duracao'] ?? 0);
            $valor = (float)($_POST['valor'] ?? 0);

            if ($nome && $duracao > 0) {
                $stmt = $pdo->prepare('UPDATE servicos SET nome_servico = ?, duracao_minutos = ?, valor = ? WHERE id = ?');
                $stmt->execute([$nome, $duracao, $valor, $id]);
                $sucesso = 'Servico atualizado.';
            } else {
                $erro = 'Preencha os campos obrigatorios.';
            }
        } elseif ($acao === 'excluir') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM servicos WHERE id = ?');
            $stmt->execute([$id]);
            $sucesso = 'Servico excluido.';
        }
    }
}

$servicos = $pdo->query('SELECT * FROM servicos ORDER BY nome_servico')->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Servicos</h1>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if ($sucesso): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

<div class="card">
    <h2>Novo Servico</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
        <input type="hidden" name="acao" value="criar">
        <div>
            <label>Nome do Servico</label>
            <input type="text" name="nome_servico" required>
        </div>
        <div>
            <label>Duracao (minutos)</label>
            <input type="number" name="duracao" required>
        </div>
        <div>
            <label>Valor (R$)</label>
            <input type="number" step="0.01" name="valor" required>
        </div>
        <button type="submit">Cadastrar</button>
    </form>
</div>

<div class="card">
    <h2>Servicos Cadastrados</h2>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Duracao</th>
                <th>Valor</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servicos as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['nome_servico']) ?></td>
                <td><?= $s['duracao_minutos'] ?> min</td>
                <td>R$ <?= number_format($s['valor'], 2, ',', '.') ?></td>
                <td>
                    <div class="acoes-btn">
                        <button type="button" class="btn-editar btn-sm" onclick="editarServico(<?= htmlspecialchars(json_encode($s)) ?>)">Editar</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirma a exclusao?')">
                            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
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
        <h2>Editar Servico</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label>Nome do Servico</label>
                <input type="text" name="nome_servico" id="edit_nome" required>
            </div>
            <div>
                <label>Duracao (minutos)</label>
                <input type="number" name="duracao" id="edit_duracao" required>
            </div>
            <div>
                <label>Valor (R$)</label>
                <input type="number" step="0.01" name="valor" id="edit_valor" required>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-secundario" onclick="fecharModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarServico(s) {
    document.getElementById('edit_id').value = s.id;
    document.getElementById('edit_nome').value = s.nome_servico;
    document.getElementById('edit_duracao').value = s.duracao_minutos;
    document.getElementById('edit_valor').value = s.valor;
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
