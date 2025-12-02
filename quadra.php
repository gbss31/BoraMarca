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
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($quadra['nome']); ?></h1>
        <a href="index.php">Voltar</a>
    </header>

   <div id="modalAgendar" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    
    <form action="reservar_quadra.php" method="POST">
      <input type="hidden" name="quadra_id" value="<?php echo $quadra['id']; ?>">
      
      <label for="data">Data:</label>
      <input type="date" name="data" required>

      <label for="hora">Hora:</label>
      <input type="time" name="hora" required>

      <label for="duracao">Duração (em horas):</label>
      <input type="number" name="duracao" min="1" max="5" required>

      <label>Forma de pagamento:</label>
     
      <label>
      <input type="radio" name="tipo_pagamento" value="sozinho" checked> 💰 Pagar sozinho 
      </label>

      <label>
      <input type="radio"  name="tipo_pagamento" value="dividido"> 👥 Dividir entre participantes
      </label>
      
      <button type="submit">Confirmar agendamento</button>

    </form>
  </div>
</div>
    
      <?php 

      $stmtaval = $pdo -> prepare("SELECT a.*, u.nome AS usuario_nome FROM avaliacoes a JOIN usuarios u ON a.usuario_id = u.id WHERE a.quadra_id = ? ORDER BY a.data_avaliacao DESC");
      $stmtaval -> execute([$id]);
      $avaliacoes = $stmtaval ->  fetchAll(PDO::FETCH_ASSOC);
      
      $stmtmedia = $pdo -> prepare("SELECT AVG(nota) AS media FROM avaliacoes WHERE quadra_id = ?");
      $stmtmedia -> execute([$id]);
      $media = $stmtmedia ->  fetch(PDO::FETCH_ASSOC)['media'];

      ?>

<main>
  <div class="quadra-card">
      <h2><?= htmlspecialchars($quadra['nome']); ?></h2>
      <p><strong> Localizacao:  </strong> <?= htmlspecialchars($quadra['localizacao'])  ?> </p>
      <p><?= nl2br(htmlspecialchars($quadra['descricao'])); ?></p>
      <p class="preco">Preço por hora: R$ <?= number_format($quadra['preco_hora'], 2, ',', '.'); ?></p>
      
  </div>

  <button id="abrirModal"> Agendar quadra </button> 

  <section class="avaliacoes"> 
      <br>
      <h3>Avaliações</h3>
       <?php if ($media): ?>
        <p class="avaliacao"> ⭐ Média: <?= number_format($media,1,',', '.') ?> /5</p>
      <?php else: ?>
        <p class="avaliacao"> ⭐ Ainda não possui avaliações </p>
      <?php endif; ?>
      <?php if (!empty($avaliacoes)): ?>
        <ul class="avaliacoes-lista">
            <?php foreach ($avaliacoes as $av): ?>
              <li>
                <strong><?= htmlspecialchars($av['usuario_nome']); ?></strong>
                ⭐<?= htmlspecialchars($av['nota']); ?>  
                <br>
                <em><?= htmlspecialchars($av['comentario']); ?></em> 
                <br>
                <small>em <?= date("d/m/y H:i", strtotime($av['data_avaliacao'])); ?></small>
              </li>
            <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Ainda não há avaliações nesta quadra</p>
      <?php endif; ?>
  </section>
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

    <script>
        const modal = document.getElementById('modalAgendar');
        const abrir = document.getElementById('abrirModal');
        const fechar = document.querySelector('.close');
        const optSolo = document.getElementById('optSolo');
        const optDividir = document.getElementById('optDividir');
        const tipoPagamento = document.getElementById('tipo_pagamento');

        abrir.onclick = () => modal.style.display = 'flex';
        fechar.onclick = () => modal.style.display = 'none';
        window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };

        function selecionarOpcao(tipo) {
            if (tipo === 'solo') {
                optSolo.classList.add('active');
                optDividir.classList.remove('active');
                tipoPagamento.value = 'solo';
            } else {
                optSolo.classList.remove('active');
                optDividir.classList.add('active');
                tipoPagamento.value = 'dividir';
            }
        }

      </script>

</body>
</html>
