<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verificarTipo('tutor');

$avisos = $pdo->query('SELECT * FROM avisos ORDER BY data_publicacao DESC')->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<h1>Mural de Avisos</h1>

<?php if (empty($avisos)): ?>
    <div class="card"><p>Nenhum aviso publicado.</p></div>
<?php else: ?>
    <?php foreach ($avisos as $av): ?>
        <div class="card">
            <h3><?= htmlspecialchars($av['titulo']) ?></h3>
            <p class="aviso-data">Publicado em <?= date('d/m/Y H:i', strtotime($av['data_publicacao'])) ?></p>
            <p style="margin-top:6px;"><?= nl2br(htmlspecialchars($av['mensagem'])) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</main>
</body>
</html>
