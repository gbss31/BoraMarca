<?php

$mensagem = "";
$sucesso = true;

$conn = new mysqli("localhost", "root", "", "aluguel_quadras");

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $sql = "SELECT email, expira_em FROM recuperacoes WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $mensagem = "Token inválido ou expirado";
        $sucesso = false;
    } else {
        $dados = $result->fetch_assoc();

        if (strtotime($dados['expira_em']) < time()) {
            $mensagem = "Token expirado";
            $sucesso = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sucesso) {
            $senha = $_POST['senha'];
            $confirmar = $_POST['confirmar_senha'];

            if ($senha !== $confirmar) {
                $mensagem = "As senhas não coincidem.";
                $sucesso = false;
            } else {
                $nova_senha = password_hash($senha, PASSWORD_DEFAULT);
                $email = $dados['email'];

              
                $sql = "UPDATE usuarios SET senha = ? WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $nova_senha, $email);
                $stmt->execute();

              
                $sql = "DELETE FROM recuperacoes WHERE token = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $token);
                $stmt->execute();

                $mensagem = "Senha alterada com sucesso!";
                $sucesso = true;

               
                $formulario_visivel = false;
            }
        }
    }
} else {
    $mensagem = "Token inválido";
    $sucesso = false;
}

$formulario_visivel = isset($dados) && strtotime($dados['expira_em']) > time() && $sucesso;

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="css/redefinir_senha.css">

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

        .mensagem {
            text-align: center;
            font-size: 18px;
            color: #dc3545;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Redefinir Senha</h1>
    <div class="form-container">
        <?php if ($formulario_visivel): ?>
            <form method="POST" action="">
                <label for="senha">Nova senha:</label>
                <input type="password" id="senha" name="senha" required>

                <label for="confirmar_senha">Confirme a nova senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>

                <button type="submit">Alterar senha</button>
            </form>

             <div class="links">
            <a href="login.php">Voltar</a><br>
             </div>
        <?php elseif (!empty($mensagem)): ?>
            <div class="mensagem"><?= $mensagem ?></div>
        <?php endif; ?>
    </div>
</div>

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
