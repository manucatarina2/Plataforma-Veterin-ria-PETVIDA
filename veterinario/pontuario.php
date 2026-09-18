<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('veterinario');

$id_agendamento = (int)($_GET['id'] ?? 0);
$id_usuario = $_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT id FROM veterinarios WHERE id_usuario = ?');
$stmt->execute([$id_usuario]);
$vet = $stmt->fetch();
$id_vet = $vet['id'] ?? 0;

$stmt = $pdo->prepare('SELECT a.*, an.nome AS animal_nome, an.id AS id_animal, an.especie, an.raca, an.sexo, an.data_nascimento, u.nome AS tutor_nome, s.nome_servico FROM agendamentos a JOIN animais an ON a.id_animal = an.id JOIN usuarios u ON an.id_tutor = u.id JOIN servicos s ON a.id_servico = s.id WHERE a.id = ? AND a.id_veterinario = ?');
$stmt->execute([$id_agendamento, $id_vet]);
$agendamento = $stmt->fetch();

if (!$agendamento) {
    header('Location: agenda.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        $erro = 'Token inválido.';
    } else {
        $peso = $_POST['peso'] ?? null;
        $sintomas = trim($_POST['sintomas'] ?? '');
        $diagnostico = trim($_POST['diagnostico'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');

        $stmt = $pdo->prepare('INSERT INTO prontuarios (id_agendamento, peso, sintomas, diagnostico, observacoes) VALUES (?, ?, ?, ?, ?)');
        if ($stmt->execute([$id_agendamento, $peso, $sintomas, $diagnostico, $observacoes])) {
            $id_prontuario = $pdo->lastInsertId();

            if (!empty($_POST['medicamentos'])) {
                foreach ($_POST['medicamentos'] as $i => $med) {
                    if (!empty($med)) {
                        $stmt = $pdo->prepare('INSERT INTO prescricoes (id_prontuario, medicamento, dosagem, periodo_tratamento) VALUES (?, ?, ?, ?)');
                        $stmt->execute([$id_prontuario, $med, $_POST['dosagens'][$i] ?? '', $_POST['periodos'][$i] ?? '']);
                    }
                }
            }

            if (!empty($_POST['id_vacina'])) {
                $stmt = $pdo->prepare('INSERT INTO vacinacoes (id_animal, id_vacina, id_veterinario, data_aplicacao, lote, data_proxima_dose) VALUES (?, ?, ?, CURDATE(), ?, ?)');
                $stmt->execute([$agendamento['id_animal'], $_POST['id_vacina'], $id_vet, $_POST['lote'] ?? '', $_POST['proxima_dose'] ?? null]);

                $stmt = $pdo->prepare('UPDATE vacinas SET quantidade_estoque = quantidade_estoque - 1 WHERE id = ? AND quantidade_estoque > 0');
                $stmt->execute([$_POST['id_vacina']]);
            }

            $stmt = $pdo->prepare('UPDATE agendamentos SET status = "concluido" WHERE id = ?');
            $stmt->execute([$id_agendamento]);

            $sucesso = 'Atendimento registrado com sucesso.';
        } else {
            $erro = 'Erro ao salvar prontuário.';
        }
    }
}

$stmt = $pdo->prepare('SELECT p.*, u.nome AS vet_nome FROM prontuarios p JOIN agendamentos a ON p.id_agendamento = a.id JOIN veterinarios v ON a.id_veterinario = v.id JOIN usuarios u ON v.id_usuario = u.id WHERE p.id_agendamento = ?');
$stmt->execute([$id_agendamento]);
$prontuario_existente = $stmt->fetch();

$vacinas = $pdo->query('SELECT * FROM vacinas WHERE quantidade_estoque > 0')->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Prontuário Eletrônico</h1>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if ($sucesso): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

<div class="card">
    <h2>Dados do Atendimento</h2>
    <p><strong>Animal:</strong> <?= htmlspecialchars($agendamento['animal_nome']) ?> (<?= htmlspecialchars($agendamento['especie']) ?>)</p>
    <p><strong>Tutor:</strong> <?= htmlspecialchars($agendamento['tutor_nome']) ?></p>
    <p><strong>Serviço:</strong> <?= htmlspecialchars($agendamento['nome_servico']) ?></p>
    <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($agendamento['data'])) ?> às <?= substr($agendamento['hora'], 0, 5) ?></p>
</div>

<?php if ($prontuario_existente): ?>
<div class="card">
    <h2>Prontuário Registrado</h2>
    <p><strong>Peso:</strong> <?= htmlspecialchars($prontuario_existente['peso']) ?> kg</p>
    <p><strong>Sintomas:</strong> <?= nl2br(htmlspecialchars($prontuario_existente['sintomas'])) ?></p>
    <p><strong>Diagnóstico:</strong> <?= nl2br(htmlspecialchars($prontuario_existente['diagnostico'])) ?></p>
    <p><strong>Observações:</strong> <?= nl2br(htmlspecialchars($prontuario_existente['observacoes'])) ?></p>
</div>
<?php else: ?>
<div class="card">
    <h2>Registrar Atendimento</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCSRF() ?>">
        <div>
            <label>Peso (kg)</label>
            <input type="number" step="0.01" name="peso">
        </div>
        <div>
            <label>Sintomas Relatados</label>
            <textarea name="sintomas" rows="3"></textarea>
        </div>
        <div>
            <label>Diagnóstico</label>
            <textarea name="diagnostico" rows="3"></textarea>
        </div>
        <div>
            <label>Observações</label>
            <textarea name="observacoes" rows="3"></textarea>
        </div>

        <h3>Vacinação (Opcional)</h3>
        <div>
            <label>Vacina Aplicada</label>
            <select name="id_vacina">
                <option value="">Nenhuma</option>
                <?php foreach ($vacinas as $v): ?>
                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nome_vacina']) ?> (<?= $v['quantidade_estoque'] ?> em estoque)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Lote</label>
            <input type="text" name="lote">
        </div>
        <div>
            <label>Data da Próxima Dose</label>
            <input type="date" name="proxima_dose">
        </div>

        <h3>Prescrições</h3>
        <div>
            <label>Medicamento</label>
            <input type="text" name="medicamentos[]">
        </div>
        <div>
            <label>Dosagem</label>
            <input type="text" name="dosagens[]">
        </div>
        <div>
            <label>Período de Tratamento</label>
            <input type="text" name="periodos[]">
        </div>

        <button type="submit">Salvar Prontuário</button>
    </form>
</div>
<?php endif; ?>
