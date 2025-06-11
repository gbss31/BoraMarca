<?php
session_start();
include 'config.php';
include 'enviar_email.php';

$mensagem = "";
$sucesso = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if ($nome && $email && $senha && $confirmar_senha) {
        if ($senha !== $confirmar_senha) {
            $mensagem = "As senhas não coincidem.";
            $sucesso = false;
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $codigo = rand(100000, 999999); 

            $verifica = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
            $verifica->execute([':email' => $email]);

            if ($verifica->rowCount() > 0) {
                $mensagem = "Este e-mail já está cadastrado.";
                $sucesso = false;
            } else {
                $sql = "INSERT INTO usuarios_pendentes (nome, email, senha_hash, codigo_verificacao) 
                        VALUES (:nome, :email, :senha, :codigo)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha', $senha_hash);
                $stmt->bindParam(':codigo', $codigo);

                if ($stmt->execute()) {
                    $assunto = "Código de verificação - BoraMarca";
                    $mensagemcorpo = "
                        <h2>Olá, $nome!</h2>
                        <p>Seu código de verificação é: <strong>$codigo</strong></p>
                        <p>Digite este código na página de verificação para ativar sua conta.</p>
                    ";

                    enviarEmail($email, $assunto, $mensagemcorpo);

                    $_SESSION['email_verificacao'] = $email;

                    header("Location: verificar_codigo.php");
                    exit;
                } else {
                    $mensagem = "Erro ao iniciar cadastro.";
                    $sucesso = false;
                }
            } 
        }
    } else {
        $mensagem = "Todos os campos são obrigatórios.";
        $sucesso = false;
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - BoraMarcar</title>
    <link rel="stylesheet" href="css/cadastro.css">
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
    <div class="container">
        <h1>Cadastro</h1>
        <form method="post" action="cadastro.php" class="form-container">
            <label>Nome:</label>
            <input type="text" name="nome" required><br><br>

            <label>Email:</label>
            <input type="email" name="email" required><br><br>

            <label>Senha:</label>
            <input type="password" name="senha" required><br><br>

            <label>Confirmar senha:</label>
            <input type="password" name="confirmar_senha" required><br><br>

            <button type="submit">Cadastrar</button>
        </form>

        <div class="links">
            <a href="login.php">Já tem uma conta? Faça login</a> <br>
            <a href="cadastro_admin.php"> É admin? Cadastrar administrador</a>
        </div>
    </div>

    <div class="rodape">
        <p>© 2025 BoraMarcar - Todos os direitos reservados.</p>
    </div>

    <!--------------------------------------------------------------------->


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

        const form = document.querySelector('form');
    form.addEventListener('submit', function (e) {
        const senha = form.querySelector('input[name="senha"]').value;
        const confirmar = form.querySelector('input[name="confirmar_senha"]').value;

        if (senha !== confirmar) {
            e.preventDefault();
            showToast("As senhas não coincidem.", false);
        }
    });
    </script>


    <!------------------------------------------------------------------->
    
</body>
</html>