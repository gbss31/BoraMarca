<?php 

session_start();

require 'config.php';


if (!isset($_SESSION['id']) || !isset($_POST['reserva_id'])) {

    header("Location: index.php");
    exit;
}

$reserva_id = $_POST['reserva_id'];
$usuario_id = $_SESSION['id'];

$sql = "SELECT r.* , q.preco_hora, q.nome AS nome_quadra
                        FROM reservas r
                        JOIN quadras q ON r.quadra_id = q.id
                        WHERE r.id = :id";

$stmt = $pdo -> prepare($sql);
$stmt -> execute (['id' => $reserva_id]);
$reserva = $stmt -> fetch(PDO::FETCH_ASSOC);

if (!$reserva) {

    die("Reserva não encontrada.");
}

$valorTotal = $reserva['preco_hora'] * $reserva['duracao'];
$valorCobrar = 0;

if ($reserva['tipo_pagamento'] === 'sozinho') {

    $valorCobrar = $valorTotal;
} else {

    $stmtPart = $pdo -> prepare ("SELECT COUNT(*) AS total FROM participantes WHERE reserva_id = ?");
    $stmtPart -> execute ([$reserva_id]);

    $totalParticipantes = $stmtPart -> fetch(PDO::FETCH_ASSOC)['total'];
    $pessoasNaSessao = $totalParticipantes + 1;
    $valorCobrar = ($pessoasNaSessao > 0) ? $valorTotal / $pessoasNaSessao : 0;
}

$valorCobrar = number_format($valorCobrar, 2, '.', '');

?>

<!DOCTYPE html>
<html lang=pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pagamento</title>
    <link rel="stylesheet" href="css/pagamento.css">

</head>
<body>

<div id="loading">
    <div class=spinner"></div>
    <p> Processando pagamento </p>
</div>

<div class="checkout-container">
    <div class="resumo">
        <h3> Resumo do pedido </h3>
        <div class="item">
            <span> Quadra: </span>
            <span> <?= htmlspecialchars($reserva['nome_quadra']) ?> </span>
    </div>
    <div class="item">
        <span> Data: </span>
        <span> <?= htmlspecialchars($reserva['data_reserva']) ?> </span>
    </div>
    <div class="item">
        <span> Horario: </span>
        <span> <?= htmlspecialchars($reserva['hora']) ?></span>
    </div>
    <div class="total"> </div>
        R$  <?= number_format($valorCobrar,2, ',', '.') ?>
    </div>

    <div class="pagamento-form">
        <h2> Pagamento </h2>

        <form id="formPagamento" action="processa_pagamento.php" method="POST">
            <input type="hidden" name="reserva_id" value="<?= $reserva_id ?> ">
            <input type="hidden" name="valor" value=" <?= $valorCobrar  ?> ">

            <div class="input-group">
                <label> Escolha o metodo </label>
                <div class="metodos-pagamento">
                    <label class="metodo active" id="btn-credito">
                        <input type="radio" name="metodo" value="credito" checked> Crédito
                    </label>
                    <label class="metodo" id="btn-debito">
                        <input type="radio" name="metodo" value="debito"> Débito
                    </label>
                </div>
            </div>

            <div class="input-group">
                <label for="nome_cartao"> Nome do Cartão </label>
                <input type="text" id="nome_cartao" name="nome_cartao" placeholder="0000 0000 0000 0000" maxlength="19" required>
            </div>

            <div class="row">
                <div class="input-group">
                    <label for="validade"> Validade</label>
                    <input type="text" id="validade" name="validade" placeholder="MM/AA" maxlength="5" required>
                </div>

                <div class="input-group">
                    <label for="cvv"> CVV</label>
                    <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4" required>
                </div>
            </div>

            <button type="submit" class="btn-finalizar"> Finalizar Pagamento</button>
        </form>
    </div>
</div>
     

        <script>


        const metodos = document.querySelectorAll('.metodo');
        metodos.forEach(m => {
            m.addEventListener('click', () => {
                metodos.forEach(x => x.classList.remove('active'));
                m.classList.add('active');
            });
        });


        const cartaoInput = document.getElementById('numero_cartao');
        cartaoInput.addEventListener('keyup', (e) => {
            let val = e.target.value.replace(/\D/g, '');
            val = val.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = val;
        })

        const form = document.getElementById('formPagamento');
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            document.getElementById('loading').style.display = 'flex';

            setTimeout(() => {
                form.submit();
            }, 2000);
        });

        </script>
</body>
</html>