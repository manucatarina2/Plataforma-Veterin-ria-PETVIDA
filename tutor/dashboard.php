<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('tutor');

$id_tutor = $_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT COUNT(*) FROM animais WHERE id_tutor = ?');
$stmt->execute([$id_tutor]);
$total_animais = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM agendamentos a JOIN animais an ON a.id_animal = an.id WHERE an.id_tutor = ? AND a.status = "agendado"');
$stmt->execute([$id_tutor]);
$total_agendamentos = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM vacinacoes v JOIN animais an ON v.id_animal = an.id WHERE an.id_tutor = ? AND v.data_proxima_dose < CURDATE()');
$stmt->execute([$id_tutor]);
$vacinas_atraso = $stmt->fetchColumn();
?>
<?php include '../includes/header.php'; ?>

<h1>Bem-vindo, <?= htmlspecialchars($_SESSION['nome']) ?></h1>

<div class="grid">
    <div class="card-stat">
        <div class="numero"><?= $total_animais ?></div>
        <div class="rotulo">Meus Animais</div>
    </div>
    <div class="card-stat">
        <div class="numero"><?= $total_agendamentos ?></div>
        <div class="rotulo">Agendamentos Ativos</div>
    </div>
    <div class="card-stat">
        <div class="numero"><?= $vacinas_atraso ?></div>
        <div class="rotulo">Vacinas em Atraso</div>
    </div>
</div>

<div class="card">
    <h2>Acesso Rapido</h2>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="meus_animais.php" class="btn">Meus Animais</a>
        <a href="agendamento.php" class="btn">Novo Agendamento</a>
        <a href="carteira_vacinacao.php" class="btn">Vacinacao</a>
    </div>
</div>

</main>
</body>
</html>
