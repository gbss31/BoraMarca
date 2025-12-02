<?php
session_start();
require 'config.php';
include 'enviar_email.php';

$mensagem = "";
$sucesso = true;

if (!isset($_SESSION['email_verificacao'])) {
    header("Location: cadastro.php");
    exit;
}

$email = $_SESSION['email_verificacao'];
$tipo = $_SESSION['tipo'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_digitado = $_POST['codigo'];
    

    $stmt = $pdo->prepare("SELECT * FROM usuarios_pendentes WHERE email = ? AND codigo_verificacao = ?  AND tipo = ?");
    $stmt->execute([$email, $codigo_digitado, $tipo]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Verifica se já existe na tabela usuarios
        $verifica = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $verifica->execute([$email]);

        if ($verifica->rowCount() == 0) {
            // Insere em usuarios
            $insert = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $insert->execute([$usuario['nome'], $usuario['email'], $usuario['senha_hash'], $tipo]);
        }

        // Remove da tabela pendente
        $pdo->prepare("DELETE FROM usuarios_pendentes WHERE email = ?")->execute([$email]);

        unset($_SESSION['email_verificacao']);
        $mensagem = "Cadastro confirmado com sucesso!";
        $sucesso = true;

        // Envia e-mail de boas-vindas
        $assunto = "Cadastro concluído - BoraMarca";
        $mensagemcorpo = "
            <h2>Olá, {$usuario['nome']}!</h2>
            <p>Sua conta no BoraMarca foi criada com sucesso!!</p>
            <p>Seja muito bem-vindo(a) e bora marcar!</p>
        ";
        enviarEmail($email, $assunto, $mensagemcorpo);

        header("refresh:3;url=login.php");
    } else {
        $mensagem = "Código incorreto. Tente novamente.";
        $sucesso = false;
    }
}
?>
<link rel="stylesheet" href="css/verificar.css">

<main>
    <div class="verificacao-card">
        <h2>Verificação de E-mail</h2>
        <form method="POST">
            <label for="codigo">Digite o código de verificação enviado ao seu e-mail:</label>
            <input type="text" name="codigo" id="codigo" required maxlength="6">
            <button type="submit">Verificar</button>
        </form>
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
</main>
