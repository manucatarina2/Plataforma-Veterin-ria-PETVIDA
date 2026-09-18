<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('tutor');

$id_tutor = $_SESSION['usuario_id'];
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        $erro = 'Token invalido.';
    } else {
        $acao = $_POST['acao'] ?? '';

        if ($acao === 'criar') {
            $nome = trim($_POST['nome'] ?? '');
            $especie = trim($_POST['especie'] ?? '');
            $raca = trim($_POST['raca'] ?? '');
            $sexo = $_POST['sexo'] ?? '';
            $data_nascimento = $_POST['data_nascimento'] ?? null;

            if ($nome && $especie && $sexo) {
                $stmt = $pdo->prepare('INSERT INTO animais (id_tutor, nome, especie, raca, sexo, data_nascimento) VALUES (?, ?, ?, ?, ?, ?)');
                if ($stmt->execute([$id_tutor, $nome, $especie, $raca, $sexo, $data_nascimento ?: null])) {
                    $sucesso = 'Animal cadastrado.';
                } else {
                    $erro = 'Erro ao cadastrar.';
                }
            } else {
                $erro = 'Preencha os campos obrigatorios.';
            }
        } elseif ($acao === 'editar') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $especie = trim($_POST['especie'] ?? '');
            $raca = trim($_POST['raca'] ?? '');
            $sexo = $_POST['sexo'] ?? '';
            $data_nascimento = $_POST['data_nascimento'] ?? null;

            if ($nome && $especie && $sexo) {
                $stmt = $pdo->prepare('UPDATE animais SET nome = ?, especie = ?, raca = ?, sexo = ?, data_nascimento = ? WHERE id = ? AND id_tutor = ?');
                $stmt->execute([$nome, $especie, $raca, $sexo, $data_nascimento ?: null, $id, $id_tutor]);
                $sucesso = 'Animal atualizado.';
            } else {
                $erro = 'Preencha os campos obrigatorios.';
            }
        } elseif ($acao === 'excluir') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM animais WHERE id = ? AND id_tutor = ?');
            $stmt->execute([$id, $id_tutor]);
            $sucesso = 'Animal removido.';
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM animais WHERE id_tutor = ? ORDER BY nome');
$stmt->execute([$id_tutor]);
$animais = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Meus Animais</h1>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if ($sucesso): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

<div class="card">
    <h2>Cadastrar Animal</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
        <input type="hidden" name="acao" value="criar">
        <div>
            <label>Nome</label>
            <input type="text" name="nome" required>
        </div>
        <div>
            <label>Especie</label>
            <select name="especie" required>
                <option value="">Selecione</option>
                <option value="Cao">Cao</option>
                <option value="Gato">Gato</option>
            </select>
        </div>
        <div>
            <label>Raca</label>
            <input type="text" name="raca">
        </div>
        <div>
            <label>Sexo</label>
            <select name="sexo" required>
                <option value="">Selecione</option>
                <option value="M">Macho</option>
                <option value="F">Femea</option>
            </select>
        </div>
        <div>
            <label>Data de Nascimento</label>
            <input type="date" name="data_nascimento">
        </div>
        <button type="submit">Cadastrar</button>
    </form>
</div>

<div class="card">
    <h2>Lista de Animais</h2>
    <?php if (empty($animais)): ?>
        <p>Nenhum animal cadastrado.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Especie</th>
                    <th>Raca</th>
                    <th>Sexo</th>
                    <th>Nascimento</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($animais as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['nome']) ?></td>
                    <td><?= htmlspecialchars($a['especie']) ?></td>
                    <td><?= htmlspecialchars($a['raca']) ?></td>
                    <td><?= $a['sexo'] === 'M' ? 'Macho' : 'Femea' ?></td>
                    <td><?= $a['data_nascimento'] ? date('d/m/Y', strtotime($a['data_nascimento'])) : '-' ?></td>
                    <td>
                        <div class="acoes-btn">
                            <button type="button" class="btn-editar btn-sm" onclick="editarAnimal(<?= htmlspecialchars(json_encode($a)) ?>)">Editar</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Confirma a exclusao?')">
                                <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn-perigo btn-sm">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="modalEditar">
    <div class="modal">
        <h2>Editar Animal</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label>Nome</label>
                <input type="text" name="nome" id="edit_nome" required>
            </div>
            <div>
                <label>Especie</label>
                <select name="especie" id="edit_especie" required>
                    <option value="Cao">Cao</option>
                    <option value="Gato">Gato</option>
                </select>
            </div>
            <div>
                <label>Raca</label>
                <input type="text" name="raca" id="edit_raca">
            </div>
            <div>
                <label>Sexo</label>
                <select name="sexo" id="edit_sexo" required>
                    <option value="M">Macho</option>
                    <option value="F">Femea</option>
                </select>
            </div>
            <div>
                <label>Data de Nascimento</label>
                <input type="date" name="data_nascimento" id="edit_nascimento">
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-secundario" onclick="fecharModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarAnimal(a) {
    document.getElementById('edit_id').value = a.id;
    document.getElementById('edit_nome').value = a.nome;
    document.getElementById('edit_especie').value = a.especie;
    document.getElementById('edit_raca').value = a.raca || '';
    document.getElementById('edit_sexo').value = a.sexo;
    document.getElementById('edit_nascimento').value = a.data_nascimento || '';
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
