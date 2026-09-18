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
            $titulo = trim($_POST['titulo'] ?? '');
            $mensagem = trim($_POST['mensagem'] ?? '');

            if ($titulo && $mensagem) {
                $stmt = $pdo->prepare('INSERT INTO avisos (titulo, mensagem) VALUES (?, ?)');
                if ($stmt->execute([$titulo, $mensagem])) {
                    $sucesso = 'Aviso publicado.';
                } else {
                    $erro = 'Erro ao publicar.';
                }
            } else {
                $erro = 'Preencha todos os campos.';
            }
        } elseif ($acao === 'editar') {
            $id = (int)($_POST['id'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? '');
            $mensagem = trim($_POST['mensagem'] ?? '');

            if ($titulo && $mensagem) {
                $stmt = $pdo->prepare('UPDATE avisos SET titulo = ?, mensagem = ? WHERE id = ?');
                $stmt->execute([$titulo, $mensagem, $id]);
                $sucesso = 'Aviso atualizado.';
            } else {
                $erro = 'Preencha todos os campos.';
            }
        } elseif ($acao === 'excluir') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM avisos WHERE id = ?');
            $stmt->execute([$id]);
            $sucesso = 'Aviso excluido.';
        }
    }
}

$avisos = $pdo->query('SELECT * FROM avisos ORDER BY data_publicacao DESC')->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Avisos</h1>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if ($sucesso): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

<div class="card">
    <h2>Novo Aviso</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
        <input type="hidden" name="acao" value="criar">
        <div>
            <label>Titulo</label>
            <input type="text" name="titulo" required>
        </div>
        <div>
            <label>Mensagem</label>
            <textarea name="mensagem" rows="4" required></textarea>
        </div>
        <button type="submit">Publicar</button>
    </form>
</div>

<div class="card">
    <h2>Avisos Publicados</h2>
    <?php if (empty($avisos)): ?>
        <p>Nenhum aviso.</p>
    <?php else: ?>
        <?php foreach ($avisos as $av): ?>
            <div class="aviso-item">
                <strong><?= htmlspecialchars($av['titulo']) ?></strong>
                <span class="aviso-data"><?= date('d/m/Y H:i', strtotime($av['data_publicacao'])) ?></span>
                <p><?= nl2br(htmlspecialchars($av['mensagem'])) ?></p>
                <div class="acoes-btn" style="margin-top:8px;">
                    <button type="button" class="btn-editar btn-sm" onclick="editarAviso(<?= htmlspecialchars(json_encode($av)) ?>)">Editar</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Confirma a exclusao?')">
                        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id" value="<?= $av['id'] ?>">
                        <button type="submit" class="btn-perigo btn-sm">Excluir</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="modalEditar">
    <div class="modal">
        <h2>Editar Aviso</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label>Titulo</label>
                <input type="text" name="titulo" id="edit_titulo" required>
            </div>
            <div>
                <label>Mensagem</label>
                <textarea name="mensagem" id="edit_mensagem" rows="4" required></textarea>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-secundario" onclick="fecharModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarAviso(av) {
    document.getElementById('edit_id').value = av.id;
    document.getElementById('edit_titulo').value = av.titulo;
    document.getElementById('edit_mensagem').value = av.mensagem;
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
