<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['id'];
$reserva_id = $_POST['reserva_id'] ?? null;

if (!$reserva_id) {
    echo "Reserva inválida.";
    exit;
}

try {
   
    $sql = "SELECT usuario_id FROM reservas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        echo "Reserva não encontrada.";
        exit;
    }

    if ($reserva['usuario_id'] == $usuario_id) {
       
        $sql = "DELETE FROM participantes WHERE reserva_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reserva_id]);

        
        $sql = "DELETE FROM convites WHERE reserva_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reserva_id]);

        
        $sql = "DELETE FROM reservas WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reserva_id]);

        header("Location: minhas_reservas.php?msg=Reserva+cancelada+com+sucesso");
        exit;

    } else {
    

        $sql = "DELETE FROM participantes WHERE reserva_id = ? AND usuario_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reserva_id, $usuario_id]);

        header("Location: minhas_reservas.php?msg=Você+saiu+da+sessão+com+sucesso");
        exit;
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
