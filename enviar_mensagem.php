<?php

session_start();

include 'config.php';

if (!isset($_SESSION['id'])) exit;

$reserva_id = $_POST['reserva_id'] ?? null;
$usuario_id = $_SESSION['id'];
$mensagem = trim($_POST['mensagem']) ?? '';


if ($mensagem !== '') {

    $stmt = $pdo -> prepare ("INSERT INTO chat_mensagens (reserva_id, usuario_id, mensagem, data_envio) VALUES (?, ?, ?, NOW())");
    $stmt -> execute([$reserva_id, $usuario_id, $mensagem]);


}
