<?php 
session_start();

include 'config.php';
include 'enviar_email.php';

$mensagem = "";
$sucesso = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $codigo_admin = $_POST['codigo_admin'];

    if (!isset($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['codigo_admin'])) {

        $mensagem =  "Preencha todos os campos.";
        $sucesso = false;

    } elseif ($_POST['codigo_admin'] !== "ADM123") {

        $mensagem = "Código de administrador inválido!";
        $sucesso = false;

    } else { 

         $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
         $codigo = rand(100000, 999999);

         $verifica = $pdo -> prepare ("SELECT * FROM usuarios WHERE email = :email");
         $verifica -> execute([':email' => $email]);

         if ($verifica -> rowCount() > 0) {

            $mensagem = "Este e-mail já está cadastrado.";
            $sucesso = false;

         } else {

            $sql = "INSERT INTO usuarios_pendentes (nome, email, senha_hash, codigo_verificacao, tipo)
                    VALUES (:nome, :email, :senha, :codigo, :tipo)";


            $stmt = $pdo -> prepare($sql);
            $stmt -> bindParam(':nome', $nome);
            $stmt -> bindParam(':email', $email);
            $stmt -> bindParam(':senha', $senha_hash);
            $stmt -> bindParam(':codigo', $codigo);

            $tipo  = 'admin';
            $stmt -> bindParam(':tipo', $tipo);

            if ($stmt -> execute()) {

                $assunto = "Código de verificação - Cadastro Admin";
                $mensagemcorpo = "<h2>Olá, $nome! </h2>
                <p> Seu código de verificação é: $codigo </p>
                <p> Para sua propia segurança não compartilhe este codigo com <strong> ninguém </strong>" ;
    
                enviarEmail($email, $assunto, $mensagemcorpo);

                $_SESSION['email_verificacao'] = $email;
                $_SESSION['tipo'] = 'admin';

                header("Location: verificar_codigo.php");
                exit;
            } else {

                $mensagem = "Erro ao iniciar cadastro";
                $sucesso = false;
            }
            
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