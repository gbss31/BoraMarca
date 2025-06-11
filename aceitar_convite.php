<?php
session_start();
include 'config.php';


if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}
?>
<link rel="stylesheet" href="css/aceitar_convite.css">
<header>
    <h1>Soccer</h1>
</header>
<main>
    <div class="card">
        <?php
        if (isset($_GET['convite_id'])) {
            $convite_id = $_GET['convite_id'];

            try {
               
                $sql = "SELECT reserva_id FROM convites WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$convite_id]);
                $convite = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($convite) {
                    $reserva_id = $convite['reserva_id'];
                    $usuario_id = $_SESSION['id'];

                 
                    $sql = "INSERT INTO participantes (reserva_id, usuario_id) VALUES (?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$reserva_id, $usuario_id]);

                    $sql = "DELETE FROM convites WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$convite_id]);

                    echo "<h2>Sucesso!</h2>";
                    echo "<p class='mensagem'>Convite aceito com sucesso!</p>";
                    echo "<a class='button' href='minhas_reservas.php'>Ir para Minhas Reservas</a>";
                } else {
                    echo "<p class='erro'>Convite não encontrado.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='erro'>Erro: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='erro'>Convite inválido.</p>";
        }
        ?>
    </div>
</main>
