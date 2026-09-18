<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('tutor');

$id_tutor = $_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT v.*, va.nome_vacina, u.nome AS vet_nome, an.nome AS animal_nome FROM vacinacoes v JOIN vacinas va ON v.id_vacina = va.id JOIN veterinarios ve ON v.id_veterinario = ve.id JOIN usuarios u ON ve.id_usuario = u.id JOIN animais an ON v.id_animal = an.id WHERE an.id_tutor = ? ORDER BY v.data_aplicacao DESC');
$stmt->execute([$id_tutor]);
$vacinacoes = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Carteira de Vacinacao</h1>

<div class="card">
    <?php if (empty($vacinacoes)): ?>
        <p>Nenhuma vacina registrada.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Animal</th>
                    <th>Vacina</th>
                    <th>Aplicacao</th>
                    <th>Lote</th>
                    <th>Veterinario</th>
                    <th>Proxima Dose</th>
                    <th>Situacao</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vacinacoes as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['animal_nome']) ?></td>
                    <td><?= htmlspecialchars($v['nome_vacina']) ?></td>
                    <td><?= date('d/m/Y', strtotime($v['data_aplicacao'])) ?></td>
                    <td><?= htmlspecialchars($v['lote']) ?></td>
                    <td><?= htmlspecialchars($v['vet_nome']) ?></td>
                    <td><?= $v['data_proxima_dose'] ? date('d/m/Y', strtotime($v['data_proxima_dose'])) : '-' ?></td>
                    <td>
                        <?php if ($v['data_proxima_dose'] && strtotime($v['data_proxima_dose']) < time()): ?>
                            <span class="badge badge-atraso">Em Atraso</span>
                        <?php elseif ($v['data_proxima_dose'] && strtotime($v['data_proxima_dose']) < strtotime('+30 days')): ?>
                            <span class="badge badge-proxima">Proxima</span>
                        <?php else: ?>
                            <span class="badge badge-ok">Em Dia</span>
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
