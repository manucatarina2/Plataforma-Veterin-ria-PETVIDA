<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PETVIDA</title>
    <link rel="stylesheet" href="/petvida/assets/css/style.css">
</head>
<body>
<header class="topo">
    <div class="logo">PETVIDA</div>
    <nav class="menu">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <?php if ($_SESSION['tipo_usuario'] === 'tutor'): ?>
                <a href="/petvida/tutor/dashboard.php">Inicio</a>
                <a href="/petvida/tutor/meus_animais.php">Meus Animais</a>
                <a href="/petvida/tutor/agendamento.php">Agendamento</a>
                <a href="/petvida/tutor/carteira_vacinacao.php">Vacinacao</a>
                <a href="/petvida/tutor/mural_avisos.php">Avisos</a>
            <?php elseif ($_SESSION['tipo_usuario'] === 'veterinario'): ?>
                <a href="/petvida/veterinario/dashboard.php">Inicio</a>
                <a href="/petvida/veterinario/agenda.php">Agenda</a>
                <a href="/petvida/veterinario/prontuario.php">Prontuario</a>
            <?php elseif ($_SESSION['tipo_usuario'] === 'admin'): ?>
                <a href="/petvida/admin/dashboard.php">Inicio</a>
                <a href="/petvida/admin/usuarios.php">Usuarios</a>
                <a href="/petvida/admin/veterinarios.php">Veterinarios</a>
                <a href="/petvida/admin/servicos.php">Servicos</a>
                <a href="/petvida/admin/avisos.php">Avisos</a>
                <a href="/petvida/admin/relatorios.php">Relatorios</a>
                <a href="/petvida/admin/estoque_vacinas.php">Estoque</a>
            <?php endif; ?>
            <a href="/petvida/logout.php">Sair</a>
        <?php else: ?>
            <a href="/petvida/login.php">Entrar</a>
            <a href="/petvida/cadastro.php">Cadastrar</a>
        <?php endif; ?>
    </nav>
</header>
<main class="conteudo">