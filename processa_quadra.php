<?php
session_start();

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: adicionar_quadra.php?msg=erro&texto=Acesso negado.");
    exit;
}

$conn = new mysqli("localhost", "root", "", "aluguel_quadras");
if ($conn->connect_error) {
    header("Location: adicionar_quadra.php?msg=erro&texto=Erro de conexão.");
    exit;
}

$nome = $_POST['nome'] ?? '';
$localizacao = $_POST['localizacao'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$preco = $_POST['preco_hora'] ?? '';

$id_admin = $_SESSION['id'];

$sql = "INSERT INTO quadras (nome, localizacao, descricao, preco_hora, id_admin) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $nome, $localizacao, $descricao, $preco, $id_admin);

if ($stmt->execute()) {
    header("Location: adicionar_quadra.php?msg=sucesso&texto=Quadra adicionada com sucesso!");
} else {
    header("Location: adicionar_quadra.php?msg=erro&texto=" . urlencode($stmt->error));
}

$stmt->close();
$conn->close();
exit;
?>
