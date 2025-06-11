<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    

  $conn = new mysqli("localhost", "root", "", "aluguel_quadras");

    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }



$sql = "SELECT id FROM usuarios WHERE email = ?";
$stmt = $conn -> prepare($sql);
$stmt -> bind_param("s", $email);
$stmt -> execute();
$stmt -> store_result();

if ($stmt ->num_rows > 0) {

    $token =bin2hex(random_bytes(50));
    $expira=date("Y-m-d H:i:s",strtotime('+1 hour'));

    $sql = "INSERT INTO recuperacoes (email, token, expira_em) VALUES (?, ?, ?)";
    $stmt = $conn -> prepare($sql);
    $stmt -> bind_param("sss", $email, $token, $expira);
    $stmt -> execute();

    include 'enviar_email.php';

    $link = "http://localhost/soccer/redefinir_senha.php?token=" . urlencode($token);

    $mensagemcorpo = "<h1> Recuperação de senha </h1>
                <p> Clique no link para redefinir sua senha: </p>
                <a href='$link'> Token </a>";

    enviarEmail($email, "Recuperação de senha", $mensagemcorpo);

    $mensagem = "Email de alteração de senha enviado";
    $sucesso = true;
} else {
    $mensagem = "Email não encontrado";
    $sucesso = false;
}
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="css/esqueci_senha.css">  

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

<title>Recuperação de Senha</title>
</head>
<body>

<div class="container">
    <h1>Recuperar Senha</h1>
    <div class="form-container">
        <form method="POST" action="">
            <label for="email">Informe seu email:</label>
            <input type="email" id="email" name="email" required />
            <button type="submit">Enviar link</button>
        </form>
    </div>
 <div class="links">
    <a href="login.php">Voltar</a>
 </div>
</body>

 <div id="toast" class="toast"></div>

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

        <?php if ($sucesso): ?>
            setTimeout(() => {
                window.locatio.href = "login.php";
            }, 4000); 
        <?php endif; ?> 

        
    </script>

</html>