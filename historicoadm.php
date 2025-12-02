<?php 

session_start();

include 'config.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
 
    header('location: login.php');
    exit;
}

$id_admin = $_SESSION['id'];

try {

    $sql_vencidas = "SELECT r.*, q.nome AS nome_quadra, u.nome AS nome_usuario
            FROM reservas r
            JOIN quadras q ON r.quadra_id = q.id
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE q.id_admin = ? AND r.data_reserva <  CURDATE() AND r.status = 'pendente'
            ORDER BY r.data_reserva DESC, r.hora DESC";

$stmt_vencidas = $pdo -> prepare($sql_vencidas);
$stmt_vencidas -> execute([$id_admin]);
$reservas_vencidas = $stmt_vencidas -> fetchAll(PDO::FETCH_ASSOC);

    $sql_finalizadas = "SELECT r.*, q.nome AS nome_quadra, u.nome AS nome_usuario
                       FROM reservas r
                       JOIN quadras q ON r.quadra_id = q.id
                       JOIN usuarios u ON r.usuario_id = u.id
                       WHERE q.id_admin = ? AND r.data_reserva < CURDATE() AND r.status = 'finalizada'
                       ORDER BY r.data_reserva DESC, r.hora DESC";

    $stmt_finalizadas = $pdo -> prepare($sql_finalizadas);
    $stmt_finalizadas -> execute([$id_admin]);
    $reservas_finalizadas = $stmt_finalizadas -> fetchAll(PDO::FETCH_ASSOC);


}catch (PDOException $e) {

    echo "Erro ao buscar reservas: " . $e -> getMessage();
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Historico de reservas</title>
    <link rel="stylesheet" href="css/historico.css">
</head>
<body>
    
   <header>

    <h1> Historico de reservas </h1>
    <a href="minhas_reservasadm.php"> Voltar </a>

   </header>

   <main> 

   <?php if (!empty($reservas_vencidas)): ?>

    <ul class="lista-reservas">
        <?php foreach ($reservas_vencidas as $reserva_v):?>
            <li class="reserva-item">
                <strong>Quadra: </strong> <?= htmlspecialchars($reserva_v['nome_quadra']) ?> <br>
                <strong>Data: </strong> <?= date('d/m/Y', strtotime($reserva_v['data_reserva'])) ?> <br>
                <strong>Hora: </strong> <?= date('H:i', strtotime($reserva_v['hora'])) ?> <br>
                <strong>Duração: </strong> <?= $reserva_v['duracao'] ?> horas <br>
                <strong> VENCIDA </strong>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

     <?php if (!empty($reservas_finalizadas)): ?>

    <ul class="lista-reservas">
        <?php foreach ($reservas_finalizadas as $reserva_f):?>
            <li class="reserva-item">
                <strong>Quadra: </strong> <?= htmlspecialchars($reserva_f['nome_quadra']) ?> <br>
                <strong>Data: </strong> <?= date('d/m/Y', strtotime($reserva_f['data_reserva'])) ?> <br>
                <strong>Hora: </strong> <?= date('H:i', strtotime($reserva_f['hora'])) ?> <br>
                <strong>Duração: </strong> <?= $reserva_f['duracao'] ?> horas <br>
                <strong>FINALIZADA</strong>
                
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php if(empty($reservas_vencidas) && empty($reservas_finalizadas)): ?>

        <p class="nenhuma"> Ainda nã há reservas passadas em suas quadras. </p>

    <?php endif; ?>

   </main>
</body>
</html>
