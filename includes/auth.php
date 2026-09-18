<?php
session_start();

function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /petvida/login.php');
        exit;
    }
}

function verificarTipo($tipo) {
    verificarLogin();
    if ($_SESSION['tipo_usuario'] !== $tipo) {
        header('Location: /petvida/login.php');
        exit;
    }
}

function gerarTokenCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}