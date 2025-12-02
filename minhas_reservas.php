<?php
session_start();
include 'config.php';  

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['id'];

try {
   
    $sql = "SELECT r.*, q.nome AS nome_quadra, q.preco_hora
            FROM reservas r
            JOIN quadras q ON r.quadra_id = q.id
            WHERE (r.usuario_id = :usuario_id
               OR r.id IN (
                   SELECT reserva_id FROM participantes WHERE usuario_id = :usuario_id))
            AND  r.data_reserva >= CURDATE()
            ORDER BY r.data_reserva DESC, r.hora DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id]);
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
    <title>Minhas Reservas</title>
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
    <h1>Minhas Reservas</h1>
    <a href="index.php">Voltar</a>
</header>

<main>
    <?php if (!empty($reservas)): ?>
        <ul class="lista-reservas">
            <?php foreach ($reservas as $reserva): ?>
                <li class="reserva-item">
                   <a href="sessao.php?reserva_id=<?= htmlspecialchars($reserva['id']) ?>" class="reserva-link">

                    <strong>Quadra:</strong> <?= htmlspecialchars($reserva['nome_quadra']) ?> <br>
                    <strong>Data:</strong> <?= date('d/m/Y', strtotime($reserva['data_reserva'])) ?><br>
                    <strong>Hora:</strong> <?= date('H:i', strtotime($reserva['hora'])) ?>

                    </a> 
                   </li>
                    <?php

                    

                    $dataHoraInicio = $reserva['data_reserva'] .  ' ' . $reserva['hora'];
                    $dataHoraFim = date('Y-m-d H:i:s', strtotime($dataHoraInicio . " + {$reserva['duracao']} hour"));
                    $agora =date ('Y-m-d H:i:s');

                    $stmtAval = $pdo -> prepare("SELECT COUNT(*) FROM avaliacoes WHERE reserva_id = ?");
                    $stmtAval -> execute ([$reserva['id']]);
                    $jaAvaliada = $stmtAval -> fetchColumn();

                    ?>


                    <br>                 

                </li>
            </li>

            <?php endforeach; ?>

        </ul>
                <?php else: ?>
                    <p>Você ainda não fez nenhuma reserva.</p>
                <?php endif; ?>

        <p><a href="historico.php"> Veja seu historico de partidas </a></p>

        </main>
    </body>
</html>
