<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id'])) {
    header('location: login.php');
    exit;
}

$usuario_id = $_SESSION['id'];

$sql = "SELECT r.*, q.nome AS nome_quadra
        FROM reservas r
        JOIN quadras q ON r.quadra_id = q.id
        WHERE (r.usuario_id = :usuario_id
            OR r.id IN (SELECT reserva_id FROM participantes WHERE usuario_id = :usuario_id))
        AND r.data_reserva < CURDATE()
        ORDER BY r.data_reserva DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['usuario_id' => $usuario_id]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Partidas</title>
    <link rel="stylesheet" href="css/historico.css">
</head>
<body>

<header>
    <h1>Histórico de Partidas</h1>
    <a href="minhas_reservas.php" class="voltar-btn">Voltar</a>
</header>

<main>
    <?php if (!empty($reservas)): ?>
        <ul class="lista-reservas">
            <?php foreach ($reservas as $reserva): ?>
            
            <?php
                $stmt = $pdo -> prepare("SELECT COUNT(*) FROM avaliacoes WHERE reserva_id = ?");
                $stmt -> execute([$reserva['id']]);
                $jaAvaliada = $stmt -> fetchColumn() > 0;
            ?>

                <li class="reserva-item">
                    <strong>Quadra:</strong> <?= htmlspecialchars($reserva['nome_quadra']) ?><br>
                    <strong>Data:</strong> <?= date('d/m/Y', strtotime($reserva['data_reserva'])) ?><br>
                    <strong>Horário:</strong> <?= date('H:i', strtotime($reserva['hora'])) ?><br>
                    <?php if (!$jaAvaliada): ?>
                    <a href="avaliar.php?reserva_id=<?= $reserva['id'] ?>" class="avaliar-btn">Avaliar a quadra</a>
                    <?php else: ?>
                    <strong> JÁ AVALIADA ✅ </strong>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="nenhuma">Você ainda não tem partidas finalizadas.</p>
    <?php endif; ?>
</main>

</body>
</html>
