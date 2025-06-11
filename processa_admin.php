<?php
session_start();


if (!isset($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['codigo_admin'])) {
    echo "Preencha todos os campos.";
    exit;
}


$conn = new mysqli("localhost", "root", "", "aluguel_quadras");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}


$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
$codigo_admin = $_POST['codigo_admin'];


if ($codigo_admin !== "ADM123") {
    echo "Código de admin inválido!";
    exit;
}

$tipo = "admin";


$sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nome, $email, $senha, $tipo);

if ($stmt->execute()) {
    echo "Administrador cadastrado com sucesso! <a href='login.php'>Fazer login</a>";
} else {
    echo "Erro ao cadastrar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
