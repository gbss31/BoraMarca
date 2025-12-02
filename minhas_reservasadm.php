<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id_admin = $_SESSION['id'];

try {
    
    $sql = "SELECT r.*, q.nome AS nome_quadra, u.nome AS nome_usuario
            FROM reservas r
            JOIN quadras q ON r.quadra_id = q.id
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE q.id_admin = ? AND r.data_reserva >=  CURDATE() AND  r.status = 'pendente'
            ORDER BY r.data_reserva DESC, r.hora DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_admin]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro ao buscar reservas: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Reservas das minhas quadras (Admin)</title>
    <link rel="stylesheet" href="css/reservas.css">
    <style>
         button {
            background-color: #201d39f2;
            color: white;
            border: none;
            padding: 8px 16px;
            margin-right: 8px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #15132a;
        }
    </style>
</head>
<body>
    <header>
        <h1>Reservas das minhas quadras</h1>
        <a href="index.php">Voltar</a>
    </header>

    <main>
        <?php if (!empty($reservas)): ?>
            <ul class="lista-reservas">
                <?php foreach ($reservas as $reserva): ?>
                    <li class="reserva-item">
                 
                        <strong>Quadra:</strong> <?= htmlspecialchars($reserva['nome_quadra']) ?><br>
                        <strong>Data:</strong> <?= date('d/m/Y', strtotime($reserva['data_reserva'])) ?><br>
                        <strong>Hora:</strong> <?= date('H:i', strtotime($reserva['hora'])) ?><br>
                        <strong>Duração:</strong> <?= $reserva['duracao'] ?> hora(s)<br>
                        <strong>Reservado por:</strong> <?= htmlspecialchars($reserva['nome_usuario']) ?>

                        <form method="post" action="finalizar_reserva.php" onsubmit="return confirm('Finalizar esta reserva?');">
                        <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                        <button type="submit">Finalizar reserva</button>
                        </form>
                    </li>
                    
                <?php endforeach; ?>

            </ul>
        <?php else: ?>
            <p>Nenhuma reserva feita nas suas quadras nos proximos dias.</p>  
        <?php endif; ?>

        <p><a href="historicoadm.php"> Veja seu historico de partidas </a></p>

    </main>

            

</body>
</html>
