<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {

    $_SESSION['id'] = $usuario['id'];
    $_SESSION['nome'] = $usuario['nome'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['tipo'] = $usuario['tipo']; 
    
    
    if ($usuario['tipo'] == 'admin') {
        header("Location: index.php");
    } else {
        header("Location: index.php");
    }
    exit;
} else {
    $mensagem = "Email ou senha inválidos.";
    $sucesso = false;
}
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - BoraMarcar</title>
    <link rel="stylesheet" href="css/login.css">

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
        <h1>Login</h1>
        <form method="post" action="login.php" class="form-container">
            <label>Email:</label>
            <input type="email" name="email" required><br><br>

            <label>Senha:</label>
            <input type="password" name="senha" required><br><br>

            <button type="submit">Entrar</button>
        </form>

        <div class="links">
            <a href="cadastro.php">Ainda não tem conta? Cadastre-se</a><br>
            <a href="esqueci_senha.php">Esqueceu a senha? Redefina </a>
        </div>
    </div>

    <div class="rodape">
        <p>© 2025 BoraMarcar - Todos os direitos reservados.</p>
    </div>

     <!-- ------------------------------------------------------------ -->

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

     <!-- --------------------------------------------------------------- -->
</body>

</html>
