<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    if ($email && $senha) {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

            if ($usuario['tipo_usuario'] === 'tutor') {
                header('Location: /petvida/tutor/dashboard.php');
            } elseif ($usuario['tipo_usuario'] === 'veterinario') {
                header('Location: /petvida/veterinario/dashboard.php');
            } else {
                header('Location: /petvida/admin/dashboard.php');
            }
            exit;
        } else {
            $erro = 'Email ou senha invalidos.';
        }
    } else {
        $erro = 'Preencha todos os campos.';
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="login-container">
    <div class="card">
        <h1>Entrar</h1>
        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
        <p style="margin-top:12px; text-align:center; font-size:13px;">
            Nao tem conta? <a href="cadastro.php">Cadastre-se</a>
        </p>
    </div>
</div>
</main>
</body>
</html>
