<?php
require_once 'includes/auth.php';
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['tipo_usuario'] === 'tutor') {
        header('Location: /petvida/tutor/dashboard.php');
    } elseif ($_SESSION['tipo_usuario'] === 'veterinario') {
        header('Location: /petvida/veterinario/dashboard.php');
    } else {
        header('Location: /petvida/admin/dashboard.php');
    }
    exit;
}
header('Location: /petvida/login.php');
exit;