<?php 

session_start();
include  'config.php'; 

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'admin') {

    header('Location: login.php');
    exit;

}

$id_admin = $_SESSION['id'];
$quadra_id = $_GET['id'] ?? null;

if (!$quadra_id) {

    $mensagem - "ID da quadra não foi encontrado.";
    $sucesso = false;       
    exit;

}

$stmt = $pdo -> prepare ("SELECT * FROM quadras WHERE id = ? AND id_admin = ?");
$stmt -> execute ([$quadra_id, $id_admin,]);
$quadra = $stmt -> fetch(PDO::FETCH_ASSOC);

if (!$quadra) {

    $mensagem = "Quadra não encontrada ou usuario sem permissão.";
    $sucesso = false;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar'])) {

    $nome= $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $localizacao = $_POST['localizacao'];

   $stmt =  $pdo -> prepare("UPDATE quadras SET nome = ?, descricao = ?, preco_hora = ?, localizacao = ? WHERE id = ? AND id_admin = ?");
   $stmt -> execute ([$nome, $descricao, $preco, $localizacao, $quadra_id, $id_admin]);

   $mensagem = "Quadra atualizada com sucesso.";
   $sucesso = true;

}

 if (isset($_POST['excluir'])) {

    $stmt = $pdo -> prepare ("DELETE FROM quadras WHERE id = ? AND id_admin = ?");
    $stmt -> execute([$quadra_id, $id_admin]);

    $mensagem = "Quadra excluida com sucesso.";
    $sucesso = true;

    sleep(seconds: 3);
    header('Location: index.php');
    exit;
 
 }

 ?>

 <!DOCTYPE html>
 <html lang="pt-BR">
 <head>
    <meta charset="UTF-8">
    <title> Gerenciar quadra </title>
    <link rel="stylesheet" href="css/admin_quadra.css">

 </head>
 <body>

 <div class="container">

     <h1> Gerenciar quadra </h1> 

     <form method="POST">

     <label for="noome"> Nome:</label>
     <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($quadra['nome']); ?>" required>

     <label for="descricao"> Descrição:</label>
     <textarea  id="descricao" name="descricao" required> <?= htmlspecialchars($quadra['descricao']); ?> </textarea> 

     <label for="preco"> Preço por hora:</label>
     <input type="number" step="0.01" id="preco" name="preco" value="<?= htmlspecialchars($quadra['preco_hora']); ?>" required>

     <label for"localizacao"> Localização: </label>
     <input type="text" id="localizacao" name="localizacao" value="<?= htmlspecialchars($quadra['localizacao']); ?>" required>

     <div class="botao">

        <button type="submit" name="atualizar"> Atualizar dados</button>
        <button type="submit" name="excluir" class="excluir" onclick="return confirm('Tem certeza que deseja excluir a quadra?')"> Deletar a quadra</button>
        
     </div>

     </form>

     <a href="index.php" class="voltar"> Voltar </a>

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