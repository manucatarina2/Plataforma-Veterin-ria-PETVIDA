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
            $nome = trim($_POST['nome_vacina'] ?? '');
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            $minimo = (int)($_POST['estoque_minimo'] ?? 5);

            if ($nome) {
                $stmt = $pdo->prepare('INSERT INTO vacinas (nome_vacina, quantidade_estoque, estoque_minimo) VALUES (?, ?, ?)');
                if ($stmt->execute([$nome, $quantidade, $minimo])) {
                    $sucesso = 'Vacina cadastrada.';
                } else {
                    $erro = 'Erro ao cadastrar.';
                }
            } else {
                $erro = 'Informe o nome da vacina.';
            }
        } elseif ($acao === 'atualizar') {
            $id = (int)($_POST['id'] ?? 0);
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            $stmt = $pdo->prepare('UPDATE vacinas SET quantidade_estoque = ? WHERE id = ?');
            $stmt->execute([$quantidade, $id]);
            $sucesso = 'Estoque atualizado.';
        } elseif ($acao === 'editar') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome_vacina'] ?? '');
            $minimo = (int)($_POST['estoque_minimo'] ?? 5);

            if ($nome) {
                $stmt = $pdo->prepare('UPDATE vacinas SET nome_vacina = ?, estoque_minimo = ? WHERE id = ?');
                $stmt->execute([$nome, $minimo, $id]);
                $sucesso = 'Vacina atualizada.';
            }
        } elseif ($acao === 'excluir') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM vacinas WHERE id = ?');
            $stmt->execute([$id]);
            $sucesso = 'Vacina excluida.';
        }
    }
}

$vacinas = $pdo->query('SELECT * FROM vacinas ORDER BY nome_vacina')->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Estoque de Vacinas</h1>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if ($sucesso): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

<div class="card">
    <h2>Cadastrar Vacina</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
        <input type="hidden" name="acao" value="criar">
        <div>
            <label>Nome da Vacina</label>
            <input type="text" name="nome_vacina" required>
        </div>
        <div>
            <label>Quantidade Inicial</label>
            <input type="number" name="quantidade" value="0">
        </div>
        <div>
            <label>Estoque Minimo</label>
            <input type="number" name="estoque_minimo" value="5">
        </div>
        <button type="submit">Cadastrar</button>
    </form>
</div>

<div class="card">
    <h2>Vacinas em Estoque</h2>
    <table>
        <thead>
            <tr>
                <th>Vacina</th>
                <th>Quantidade</th>
                <th>Minimo</th>
                <th>Situacao</th>
                <th>Atualizar Qtd</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vacinas as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['nome_vacina']) ?></td>
                <td><?= $v['quantidade_estoque'] ?></td>
                <td><?= $v['estoque_minimo'] ?></td>
                <td>
                    <?php if ($v['quantidade_estoque'] <= $v['estoque_minimo']): ?>
                        <span class="badge badge-atraso">Baixo</span>
                    <?php else: ?>
                        <span class="badge badge-ok">Normal</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="POST" style="display:flex; gap:4px; align-items:center;">
                        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
                        <input type="hidden" name="acao" value="atualizar">
                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                        <input type="number" name="quantidade" value="<?= $v['quantidade_estoque'] ?>" style="width:70px; padding:4px 6px;">
                        <button type="submit" class="btn-sm">Salvar</button>
                    </form>
                </td>
                <td>
                    <div class="acoes-btn">
                        <button type="button" class="btn-editar btn-sm" onclick="editarVacina(<?= htmlspecialchars(json_encode($v)) ?>)">Editar</button>
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
        <h2>Editar Vacina</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label>Nome da Vacina</label>
                <input type="text" name="nome_vacina" id="edit_nome" required>
            </div>
            <div>
                <label>Estoque Minimo</label>
                <input type="number" name="estoque_minimo" id="edit_minimo">
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-secundario" onclick="fecharModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarVacina(v) {
    document.getElementById('edit_id').value = v.id;
    document.getElementById('edit_nome').value = v.nome_vacina;
    document.getElementById('edit_minimo').value = v.estoque_minimo;
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
