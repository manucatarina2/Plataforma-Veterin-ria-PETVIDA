<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    $telefone = trim($_POST['telefone'] ?? '');

    if ($nome && $email && $senha) {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = 'Este email ja esta cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, telefone, tipo_usuario) VALUES (?, ?, ?, ?, "tutor")');
            if ($stmt->execute([$nome, $email, $hash, $telefone])) {
                $sucesso = 'Cadastro realizado. Faca login.';
            } else {
                $erro = 'Erro ao cadastrar.';
            }
        }
    } else {
        $erro = 'Preencha todos os campos obrigatorios.';
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="login-container">
    <div class="card">
        <h1>Cadastro</h1>
        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div>
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <div>
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone">
            </div>
            <button type="submit">Cadastrar</button>
        </form>
        <p style="margin-top:12px; text-align:center; font-size:13px;">
            Ja tem conta? <a href="login.php">Entrar</a>
        </p>
    </div>
</div>
</main>
</body>
</html>
