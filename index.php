<?php

session_start();

include 'config.php';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>BoraMarca</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    
    <div class="logo">
        <img src="BoraMarca!2.png" alt="Logo do Site">
    </div>

      <div class="saudacao">
        <?php if (isset($_SESSION['nome'])): ?>
            <?php if ($_SESSION['tipo'] == 'admin'): ?>
                <p>Olá <?php echo htmlspecialchars($_SESSION['nome']); ?>!</p>
            <?php else: ?>
                <p>Opa, <?php echo htmlspecialchars($_SESSION['nome']); ?> bora marcar?!</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="user-links">
        <?php if (!isset($_SESSION['nome'])): ?>
            <a href="login.php">Login</a> |
            <a href="cadastro.php">Cadastre-se</a>
        <?php elseif ($_SESSION['tipo'] == 'admin'): ?>
            <a href="adicionar_quadra.php">Adicionar Quadra</a> |
            <a href="minhas_reservasadm.php">Minhas Reservas</a> |
            <a href="logout.php">Sair</a>
        <?php else: ?>
            <a href="minhas_reservas.php">Minhas Reservas</a> |
            <a href="logout.php">Sair</a>
        <?php endif; ?>
    </div>

</header>

<main>

<?php

try {
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin') {
        
        $id_admin = $_SESSION['id'];
        $stmt = $pdo->prepare("SELECT * FROM quadras WHERE id_admin = ?");
        $stmt->execute([$id_admin]);
    } else {
       
        $stmt = $pdo->query("SELECT * FROM quadras");
    }
    $quadras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao buscar quadras: " . $e->getMessage();
}

?>

<h2>
    <?php 
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin') {
        echo "Minhas Quadras";
    } else {
        echo "Quadras disponíveis";
    }
    ?>
</h2>

<?php if (!empty($quadras)): ?>
    <div class="quadras-container">
        <?php foreach ($quadras as $quadra): ?>
            <div class="quadra-card">
                <h3>
                    <a href="quadra.php?id=<?php echo $quadra['id']; ?>">
                        <?php echo htmlspecialchars($quadra['nome']); ?>
                    </a>
                </h3>
                <p><?php echo nl2br(htmlspecialchars($quadra['descricao'])); ?></p>
                <p class="preco">R$ <?php echo number_format($quadra['preco_hora'], 2, ',', '.'); ?> por hora</p>
            </div>
        <?php endforeach; ?>
    </div>
    
<?php else: ?>
    <p>Nenhuma quadra foi encontrada.</p>
<?php endif; ?>

</main>

</body>
</html>
