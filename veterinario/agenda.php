<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('veterinario');

$id_usuario = $_SESSION['usuario_id'];
$stmt = $pdo->prepare('SELECT id FROM veterinarios WHERE id_usuario = ?');
$stmt->execute([$id_usuario]);
$vet = $stmt->fetch();
$id_vet = $vet['id'] ?? 0;

$stmt = $pdo->prepare('SELECT a.*, an.nome AS animal_nome, an.especie, s.nome_servico, u.nome AS tutor_nome FROM agendamentos a JOIN animais an ON a.id_animal = an.id JOIN servicos s ON a.id_servico = s.id JOIN usuarios u ON an.id_tutor = u.id WHERE a.id_veterinario = ? AND a.data >= CURDATE() AND a.status != "cancelado" ORDER BY a.data, a.hora');
$stmt->execute([$id_vet]);
$agenda = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Minha Agenda</h1>

<div class="card">
    <?php if (empty($agenda)): ?>
        <p>Nenhum atendimento agendado.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Animal</th>
                    <th>Espécie</th>
                    <th>Tutor</th>
                    <th>Serviço</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agenda as $ag): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($ag['data'])) ?></td>
                    <td><?= substr($ag['hora'], 0, 5) ?></td>
                    <td><?= htmlspecialchars($ag['animal_nome']) ?></td>
                    <td><?= htmlspecialchars($ag['especie']) ?></td>
                    <td><?= htmlspecialchars($ag['tutor_nome']) ?></td>
                    <td><?= htmlspecialchars($ag['nome_servico']) ?></td>
                    <td><?= htmlspecialchars($ag['status']) ?></td>
                    <td><a href="prontuario.php?id=<?= $ag['id'] ?>" class="btn" style="padding:6px 14px; font-size:12px;">Atender</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
