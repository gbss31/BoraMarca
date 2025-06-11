<?php
session_start();
require 'config.php';

$mensagem = "";
$sucesso = true;

if (!isset($_SESSION['email_verificacao'])) {
    header("Location: cadastro.php");
    exit;
}

$email = $_SESSION['email_verificacao'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_digitado = $_POST['codigo'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios_pendentes WHERE email = ? AND codigo_verificacao = ?");
    $stmt->execute([$email, $codigo_digitado]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute([$usuario['nome'], $usuario['email'], $usuario['senha_hash']]);

        
        $pdo->prepare("DELETE FROM usuarios_pendentes WHERE email = ?")->execute([$email]);

        unset($_SESSION['email_verificacao']);
        $mensagem = "Cadastro confirmado com sucesso!";
        $sucesso = true;
        sleep(6);
        header("Location: login.php");
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
