<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('admin');

$total_usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$total_animais = $pdo->query('SELECT COUNT(*) FROM animais')->fetchColumn();
$total_agendamentos = $pdo->query('SELECT COUNT(*) FROM agendamentos WHERE status = "agendado"')->fetchColumn();
$vacinas_baixo = $pdo->query('SELECT COUNT(*) FROM vacinas WHERE quantidade_estoque <= estoque_minimo')->fetchColumn();
?>
<?php include '../includes/header.php'; ?>

<h1>Painel Administrativo</h1>

<div class="grid">
    <div class="card-stat">
        <div class="numero"><?= $total_usuarios ?></div>
        <div class="rotulo">Usuarios</div>
    </div>
    <div class="card-stat">
        <div class="numero"><?= $total_animais ?></div>
        <div class="rotulo">Animais</div>
    </div>
    <div class="card-stat">
        <div class="numero"><?= $total_agendamentos ?></div>
        <div class="rotulo">Agendamentos Ativos</div>
    </div>
    <div class="card-stat">
        <div class="numero"><?= $vacinas_baixo ?></div>
        <div class="rotulo">Vacinas Estoque Baixo</div>
    </div>
</div>

</main>
</body>
</html>
