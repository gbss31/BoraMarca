<?php 
session_start();
include 'config.php';

$mensagem = "";
$sucesso = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['codigo_admin'])) {
        $mensagem =  "Preencha todos os campos.";
        $sucesso = false;
    } elseif ($_POST['codigo_admin'] !== "ADM123") {
        $mensagem = "Código de administrador inválido!";
        $sucesso = false;
    } else {
        $_SESSION['cadastro'] = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'senha' => password_hash($_POST['senha'], PASSWORD_DEFAULT),
            'tipo' => 'admin'
        ];

        $codigo = rand(100000, 999999);
        $_SESSION['codigo_verificacao'] = $codigo;

        // Envia o e-mail
        $para = $_POST['email'];
        $assunto = "Código de verificação - Cadastro Admin";
        $mensagemEmail = "Seu código de verificação é: $codigo";
        $cabecalhos = "From: sistema@aluguelquadras.com";

        if (mail($para, $assunto, $mensagemEmail, $cabecalhos)) {
            header("Location: verificar_codigo.php");
            exit;
        } else {
            $mensagem = "Erro ao enviar o código por e-mail.";
            $sucesso = false;
        }
    }

}

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/cadastroadm.css"> 
    <title>Cadastro administrador</title>
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

 <h2> Cadastro de adminstradores de quadra </h2>

 <form method="POST" action="cadastro_admin.php">
 
    <label> Nome: </label>
    <input type="text" name="nome" placeholder="Nome" required>
    <label> Email: </label>
    <input type="email" name="email" placeholder="Email" required>
    <label> Senha:</label>
    <input type="password" name="senha" placeholder="Senha" required>
    <label> Codigo de admin: </label>
    <input type="text" name="codigo_admin" placeholder="Codigo do admin" required>
    <button type="submit"> Cadastrar admin </button> 

     </form>  

     <br>
     <a href="login.php"> Voltar </a>

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
    </script>

    </body>
</html>