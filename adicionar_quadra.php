<?php
session_start();
include 'config.php';

$mensagem = "";
$sucesso = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['nome'], $_POST['localizacao'], $_POST['descricao'], $_POST['preco_hora'])) {
        $mensagem = "Preencha todos os campos";
        $sucesso = false;
    } else {
        $conn = new mysqli("localhost", "root", "", "aluguel_quadras");
        if ($conn -> connect_error) {
            die("Erro na conexão: " . $conn -> connect_error);
        }

        $nome = $_POST['nome'];
        $localizacao = $_POST['localizacao'];
        $descricao = $_POST['descricao'];
        $preco = $_POST['preco_hora'];

        $id_admin = $_SESSION['id'];

        $sql = "INSERT INTO quadras (nome, localizacao, descricao, preco_hora, id_admin) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn -> prepare($sql);
        $stmt -> bind_param("ssssi", $nome, $localizacao, $descricao, $preco, $id_admin);

        if ($stmt -> execute()) {
            $mensagem = "Quadra adicionada com sucesso!";
            $sucesso = true;
        } else {
            $mensagem = "Erro ao adicionar quadra: " . $stmt -> error;
            $sucesso = false;
        }

        
    }
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Quadra</title>
    <link rel="stylesheet" href="css/adcquadra.css">
    <style>
        .toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 12px;
            padding: 16px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
            opacity: 0;
            transition: opacity 0.5s, bottom 0.5s;
        }

        .toast.show {
            visibility: visible;
            opacity: 1;
            bottom: 50px;
        }
    </style>
</head>
<body>

<header>
    <h1>Adicionar Quadra</h1>
</header>

<main>
    <form action="adicionar_quadra.php" method="POST">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="localizacao">Localização:</label>
        <input type="text" id="localizacao" name="localizacao" required>

        <label for="descricao">Descrição:</label>
        <input type="text" id="descricao" name="descricao" required>

        <label for="preco_hora">Preço por hora:</label>
        <input type="number" id="preco_hora" name="preco_hora" required>

        <button type="submit">Cadastrar Quadra</button>
    </form>
</main>

<br>

 <a href="index.php">Voltar</a>

 <div id ="toast" class="toast"></div>
 <script>
        function showToast(message, isSuccess = true) {
            const toast = document.getElementById("toast");
            toast.innerText = message;
            toast.style.backgroundColor = isSuccess ? "#28a745" : "#dc3545";
            toast.className = "toast show";
            setTimeout(() => { toast.className = "toast"; }, 4000);
        }

        <?php if (!empty($mensagem)): ?>
            showToast("<?= $mensagem ?>", <?= $sucesso ? 'true' : 'false' ?>);
        <?php endif; ?>
    </script>
    

</body>
</html>
