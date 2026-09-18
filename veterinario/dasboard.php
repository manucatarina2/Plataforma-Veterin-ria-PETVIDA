<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('veterinario');

$id_usuario = $_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT id FROM veterinarios WHERE id_usuario = ?');
$stmt->execute([$id_usuario]);
$vet = $stmt->fetch();
$id_vet = $vet['id'] ?? 0;

$stmt = $pdo->prepare('SELECT COUNT(*) FROM agendamentos WHERE id_veterinario = ? AND data = CURDATE() AND status != "cancelado"');
$stmt->execute([$id_vet]);
$hoje = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM agendamentos WHERE id_veterinario = ? AND status = "agendado"');
$stmt->execute([$id_vet]);
$pendentes = $stmt->fetchColumn();
?>
<?php include '../includes/header.php'; ?>

<h1>Painel do Veterinário</h1>

<div class="grid">
    <div class="card-stat">
        <div class="numero"><?= $hoje ?></div>
        <div class="rotulo">Atendimentos Hoje</div>
    </div>
    <div class="card-stat">
        <div class="numero"><?= $pendentes ?></div>
        <div class="rotulo">Agendamentos Pendentes</div>
    </div>
</div>

<div class="card">
    <h2>Ações</h2>
    <a href="agenda.php" class="btn">Ver Agenda</a>
</div>
