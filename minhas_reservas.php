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
            WHERE r.usuario_id = :usuario_id
               OR r.id IN (
                   SELECT reserva_id FROM participantes WHERE usuario_id = :usuario_id
               )
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
    <link rel="stylesheet" href="css/style.css">
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
                    <strong>Quadra:</strong> <?= htmlspecialchars($reserva['nome_quadra']) ?><br>
                    <strong>Data:</strong> <?= date('d/m/Y', strtotime($reserva['data_reserva'])) ?><br>
                    <strong>Hora:</strong> <?= date('H:i', strtotime($reserva['hora'])) ?><br>
                    <strong>Duração:</strong> <?= $reserva['duracao'] ?> hora(s)<br>

                    <?php
                    
                    $sqlParticipantes = "SELECT COUNT(*) AS total FROM participantes WHERE reserva_id = ?";
                    $stmtParticipantes = $pdo->prepare($sqlParticipantes);
                    $stmtParticipantes->execute([$reserva['id']]);
                    $totalParticipantes = $stmtParticipantes->fetch(PDO::FETCH_ASSOC)['total'];

                 
                    $pessoasNaSessao = $totalParticipantes + 1;

                    $valorTotal = $reserva['preco_hora'] * $reserva['duracao'];

                    $valorPorPessoa = ($pessoasNaSessao > 0) ? $valorTotal / $pessoasNaSessao : 0;
                    ?>

                    <strong>Pessoas nesta sessão:</strong> <?= $pessoasNaSessao ?><br>
                    <strong>Valor total da reserva:</strong> R$ <?= number_format($valorTotal, 2, ',', '.') ?><br>
                    <strong>Valor por pessoa:</strong> R$ <?= number_format($valorPorPessoa, 2, ',', '.') ?><br>

                    <br> 
                    
                    
                    <form action="desmarcar_reserva.php" method="POST" style="display:inline;">
                        <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                        <button type="submit">Desmarcar</button>
                    </form>

                   
                    <form action="gerar_link.php" method="POST" style="display:inline;">
                        <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                        <button type="submit">Gerar Link</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Você ainda não fez nenhuma reserva.</p>
    <?php endif; ?>
</main>
</body>
</html>
