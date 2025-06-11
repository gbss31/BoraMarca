<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$reserva_id = $_POST['reserva_id'] ?? '';

?>
<link rel="stylesheet" href="css/link.css">
<header>
    <h1>Soccer</h1>
</header>
<main>
    <div class="card">
        <?php
        if ($reserva_id) {
            try {
                $convidado_id = NULL;

                $sql = "INSERT INTO convites (reserva_id, convidado_id, status) VALUES (?, ?, 'pendente')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$reserva_id, $convidado_id]);

                $convite_id = $pdo->lastInsertId();

                $link = "http://localhost/soccer/aceitar_convite.php?convite_id=" . urlencode($convite_id);

                echo "<h2>Link gerado</h2>";
                echo "<input type='text' value='$link' readonly>";
                echo "<a class='button' href='minhas_reservas.php'>Voltar</a>";

            } catch (PDOException $e) {
                echo "<p class='erro'>Erro ao gerar convite: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='erro'>Reserva inválida.</p>";
        }
        ?>
    </div>
</main>
