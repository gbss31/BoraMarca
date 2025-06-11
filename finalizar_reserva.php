<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reserva_id'])) {
    $reserva_id = intval($_POST['reserva_id']);
    $id_admin = $_SESSION['id'];

    try {
 
        $sql_check = "SELECT r.id FROM reservas r
                      JOIN quadras q ON r.quadra_id = q.id
                      WHERE r.id = ? AND q.id_admin = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$reserva_id, $id_admin]);
        if ($stmt_check->rowCount() === 0) {
            throw new Exception("Reserva não encontrada ou você não tem permissão.");
        }

      
        $sql_update = "UPDATE reservas SET status = 'finalizada' WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$reserva_id]);

        $_SESSION['msg_sucesso'] = "Reserva finalizada com sucesso!";
    } catch (Exception $e) {
        $_SESSION['msg_erro'] = "Erro: " . $e->getMessage();
    }
} else {
    $_SESSION['msg_erro'] = "ID da reserva inválido.";
}


header('Location: minhas_reservasadm.php');
exit;
?>
