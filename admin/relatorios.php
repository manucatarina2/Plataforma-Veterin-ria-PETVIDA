<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('admin');

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fim = $_GET['fim'] ?? date('Y-m-d');

$stmt = $pdo->prepare('SELECT COUNT(*) FROM agendamentos WHERE data BETWEEN ? AND ?');
$stmt->execute([$inicio, $fim]);
$total_atendimentos = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT s.nome_servico, COUNT(*) AS total FROM agendamentos a JOIN servicos s ON a.id_servico = s.id WHERE a.data BETWEEN ? AND ? GROUP BY s.id ORDER BY total DESC');
$stmt->execute([$inicio, $fim]);
$servicos_mais = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT u.nome, COUNT(*) AS total FROM agendamentos a JOIN veterinarios v ON a.id_veterinario = v.id JOIN usuarios u ON v.id_usuario = u.id WHERE a.data BETWEEN ? AND ? GROUP BY v.id ORDER BY total DESC');
$stmt->execute([$inicio, $fim]);
$por_veterinario = $stmt->fetchAll();

$vacinas_atraso = $pdo->query('SELECT COUNT(*) FROM vacinacoes WHERE data_proxima_dose < CURDATE()')->fetchColumn();
?>
<?php include '../includes/header.php'; ?>

<h1>Relatorios</h1>

<div class="card">
    <h2>Filtro por Periodo</h2>
    <form method="GET" style="flex-direction:row; align-items:flex-end; gap:12px; max-width:none;">
        <div>
            <label>Inicio</label>
            <input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>">
        </div>
        <div>
            <label>Fim</label>
            <input type="date" name="fim" value="<?= htmlspecialchars($fim) ?>">
        </div>
        <button type="submit">Filtrar</button>
    </form>
</div>

<div class="grid">
    <div class="card-stat">
        <div class="numero"><?= $total_atendimentos ?></div>
        <div class="rotulo">Atendimentos no Periodo</div>
    </div>
    <div class="card-stat">
        <div class="numero"><?= $vacinas_atraso ?></div>
        <div class="rotulo">Vacinas em Atraso</div>
    </div>
</div>

<div class="card">
    <h2>Servicos Mais Procurados</h2>
    <?php if (empty($servicos_mais)): ?>
        <p>Nenhum dado no periodo.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Servico</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servicos_mais as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['nome_servico']) ?></td>
                <td><?= $s['total'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Atendimentos por Veterinario</h2>
    <?php if (empty($por_veterinario)): ?>
        <p>Nenhum dado no periodo.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Veterinario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($por_veterinario as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['nome']) ?></td>
                <td><?= $v['total'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

</main>
</body>
</html>
