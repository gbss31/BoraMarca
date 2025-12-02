<?php 

session_start();

include 'config.php';


if (!isset($_GET['reserva_id'])) {

    header('Content-Type: application/json; charsert-utf-8');
    echo json_encode([]);
    exit;

}

$reserva_id = (int)$_GET['reserva_id'];

header('Content-Type: application/json; charsert-utf-8');

try {
$stmt = $pdo -> prepare ("SELECT m.* , u.nome AS nome_usuario FROM chat_mensagens m JOIN usuarios u ON m.usuario_id = u.id
                            WHERE m.reserva_id = ? ORDER BY m.data_envio ASC");

$stmt -> execute ([$reserva_id]);
$mensagens = $stmt -> fetchALL(PDO::FETCH_ASSOC);
echo json_encode($mensagens);

} catch (PDOException $e) {

    echo json_encode(['erro' =>  $e -> getMessage()]);
}




