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
            $id_animal = (int)($_POST['id_animal'] ?? 0);
            $id_veterinario = (int)($_POST['id_veterinario'] ?? 0);
            $id_servico = (int)($_POST['id_servico'] ?? 0);
            $data = $_POST['data'] ?? '';
            $hora = $_POST['hora'] ?? '';

            if ($id_animal && $id_veterinario && $id_servico && $data && $hora) {
                $stmt = $pdo->prepare('SELECT id FROM agendamentos WHERE id_veterinario = ? AND data = ? AND hora = ? AND status != "cancelado"');
                $stmt->execute([$id_veterinario, $data, $hora]);

                if ($stmt->fetch()) {
                    $erro = 'Horario ja ocupado para este veterinario.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO agendamentos (id_animal, id_veterinario, id_servico, data, hora) VALUES (?, ?, ?, ?, ?)');
                    if ($stmt->execute([$id_animal, $id_veterinario, $id_servico, $data, $hora])) {
                        $sucesso = 'Agendamento realizado.';
                    } else {
                        $erro = 'Erro ao agendar.';
                    }
                }
            } else {
                $erro = 'Preencha todos os campos.';
            }
        } elseif ($acao === 'cancelar') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('UPDATE agendamentos SET status = "cancelado" WHERE id = ? AND id_animal IN (SELECT id FROM animais WHERE id_tutor = ?)');
            $stmt->execute([$id, $id_tutor]);
            $sucesso = 'Agendamento cancelado.';
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM animais WHERE id_tutor = ? ORDER BY nome');
$stmt->execute([$id_tutor]);
$animais = $stmt->fetchAll();

$veterinarios = $pdo->query('SELECT v.id, u.nome, v.especialidade FROM veterinarios v JOIN usuarios u ON v.id_usuario = u.id ORDER BY u.nome')->fetchAll();

$servicos = $pdo->query('SELECT * FROM servicos ORDER BY nome_servico')->fetchAll();

$stmt = $pdo->prepare('SELECT a.*, an.nome AS animal_nome, s.nome_servico, u.nome AS vet_nome FROM agendamentos a JOIN animais an ON a.id_animal = an.id JOIN servicos s ON a.id_servico = s.id JOIN veterinarios v ON a.id_veterinario = v.id JOIN usuarios u ON v.id_usuario = u.id WHERE an.id_tutor = ? ORDER BY a.data DESC, a.hora DESC');
$stmt->execute([$id_tutor]);
$agendamentos = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Agendamento</h1>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if ($sucesso): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

<div class="card">
    <h2>Novo Agendamento</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
        <input type="hidden" name="acao" value="criar">
        <div>
            <label>Animal</label>
            <select name="id_animal" required>
                <option value="">Selecione</option>
                <?php foreach ($animais as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Servico</label>
            <select name="id_servico" required>
                <option value="">Selecione</option>
                <?php foreach ($servicos as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nome_servico']) ?> - R$ <?= number_format($s['valor'], 2, ',', '.') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Veterinario</label>
            <select name="id_veterinario" required>
                <option value="">Selecione</option>
                <?php foreach ($veterinarios as $v): ?>
                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nome']) ?> - <?= htmlspecialchars($v['especialidade']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Data</label>
            <input type="date" name="data" min="<?= date('Y-m-d') ?>" required>
        </div>
        <div>
            <label>Hora</label>
            <input type="time" name="hora" required>
        </div>
        <button type="submit">Agendar</button>
    </form>
</div>

<div class="card">
    <h2>Meus Agendamentos</h2>
    <?php if (empty($agendamentos)): ?>
        <p>Nenhum agendamento.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Animal</th>
                    <th>Servico</th>
                    <th>Veterinario</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Status</th>
                    <th>Acao</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agendamentos as $ag): ?>
                <tr>
                    <td><?= htmlspecialchars($ag['animal_nome']) ?></td>
                    <td><?= htmlspecialchars($ag['nome_servico']) ?></td>
                    <td><?= htmlspecialchars($ag['vet_nome']) ?></td>
                    <td><?= date('d/m/Y', strtotime($ag['data'])) ?></td>
                    <td><?= substr($ag['hora'], 0, 5) ?></td>
                    <td><?= htmlspecialchars($ag['status']) ?></td>
                    <td>
                        <?php if ($ag['status'] === 'agendado'): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Cancelar este agendamento?')">
                            <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
                            <input type="hidden" name="acao" value="cancelar">
                            <input type="hidden" name="id" value="<?= $ag['id'] ?>">
                            <button type="submit" class="btn-perigo btn-sm">Cancelar</button>
                        </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</main>
</body>
</html>
