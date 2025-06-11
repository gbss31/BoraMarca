<?php 
session_start();
include 'config.php';


if (!isset($_SESSION['nome'])) {
    header('Location: login.php');
    exit;
}


if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID da Quadra selecionada não foi fornecido :C";
    exit;
}

$id = $_GET['id'];


$reserva_sucesso = isset($_GET['reserva']) && $_GET['reserva'] === 'sucesso';
$reserva_erro = isset($_GET['reserva']) && $_GET['reserva'] === 'erro';

try {
    $stmt = $pdo->prepare("SELECT * FROM quadras WHERE id = ?");
    $stmt->execute([$id]);
    $quadra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quadra) {
        echo "A quadra não foi encontrada :C";
        exit;
    } 
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($quadra['nome']); ?></title>
    <link rel="stylesheet" href="css/quadra.css">

    <style>
      #toast {
        visibility: hidden;
        min-width: 250px;
        color: white;
        text-align: center;
        border-radius: 5px;
        padding: 16px;
        position: fixed;
        left: 50%;
        bottom: 30px;
        font-size: 17px;
        transform: translateX(-50%);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
      }
      #toast.show {
        visibility: visible;
        opacity: 1;
      }
      #toast.success {
        background-color: #4BB543; 
      }
      #toast.error {
        background-color: #dc3545; 
      }
    </style>
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($quadra['nome']); ?></h1>
        <a href="index.php">Voltar</a>
    </header>

    <main>
        <div class="quadra-card">
            <h2><?php echo htmlspecialchars($quadra['nome']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($quadra['descricao'])); ?></p>
            <p class="preco">Preço por hora: R$ <?php echo number_format($quadra['preco_hora'], 2, ',', '.'); ?></p>
        </div>

        <div class="form-container">
            <h3>Preencha para reservar a quadra</h3>
            <form method="POST" action="reservar_quadra.php">
                <input type="hidden" name="quadra_id" value="<?php echo $quadra['id']; ?>">

                <label for="data">Data:</label>
                <input type="date" name="data" required>

                <label for="hora">Hora:</label>
                <input type="time" name="hora" required>

                <label for="duracao">Duração (em horas):</label>
                <input type="number" name="duracao" min="1" max="5" required>

                <button type="submit">Reservar</button>
            </form>
        </div>
    </main>

    <?php if ($reserva_sucesso): ?>
      <div id="toast" class="success">Reserva concluída com sucesso!</div>
      <script>
        const toastSuccess = document.getElementById('toast');
        toastSuccess.classList.add('show');
        setTimeout(() => {
          toastSuccess.classList.remove('show');
          window.location.href = 'index.php'; 
        }, 3000);
      </script>
    <?php elseif ($reserva_erro): ?>
      <div id="toast" class="error">Já existe uma reserva para essa data e hora!</div>
      <script>
        const toastError = document.getElementById('toast');
        toastError.classList.add('show');
        setTimeout(() => {
          toastError.classList.remove('show');
          
        }, 3000);
      </script>
    <?php endif; ?>
</body>
</html>
