<?php

session_start();
require 'config.php';

if (!isset($_SESSION['id']) || empty($_POST['reserva_id'])) {
    header("Location: index.php");
    exit;
}

$reserva_id = $_POST['reserva_id'];
$usuario_id = $_SESSION['id'];
$valor_pago = $_POST['valor'];
$metodo = $_POST['metodo'];


$stmtInfo = $pdo->prepare("SELECT tipo_pagamento, usuario_id AS criador_id FROM reservas WHERE id = :id");
$stmtInfo->execute(['id' => $reserva_id]);
$reservaInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

if (!$reservaInfo) {
    die("Erro: Reserva não encontrada.");
}

$tipo_pagamento = $reservaInfo['tipo_pagamento'];
$criador_id = $reservaInfo['criador_id'];

try {
    $pdo->beginTransaction();

   
    $stmtPag = $pdo->prepare ("
        INSERT INTO pagamentos (reserva_id, usuario_id, valor_total, data_pagamento, metodo, status) 
        VALUES (:reserva_id, :usuario_id, :valor, NOW(), :metodo, 'pago')
    ");

    $stmtPag->execute([
        'reserva_id' => $reserva_id,
        'usuario_id' => $usuario_id,
        'valor'      => $valor_pago,
        'metodo'     => $metodo
    ]);


    if ($tipo_pagamento === 'sozinho') {
        
       
        $stmtReserva = $pdo->prepare("
            UPDATE reservas 
            SET status = 'confirmada' 
            WHERE id = :reserva_id
        ");
        $stmtReserva->execute(['reserva_id' => $reserva_id]);

    } else {
    
        $stmtPagos = $pdo->prepare("
            SELECT COUNT(DISTINCT usuario_id) AS num_pagamentos 
            FROM pagamentos
            WHERE reserva_id = :reserva_id AND status = 'pago'
        ");
        $stmtPagos->execute(['reserva_id' => $reserva_id]);
        $numPagamentos = $stmtPagos->fetch(PDO::FETCH_ASSOC)['num_pagamentos'];

       
        $stmtParticipantes = $pdo->prepare("SELECT COUNT(*) AS total_participantes FROM participantes WHERE reserva_id = :reserva_id");
        $stmtParticipantes->execute(['reserva_id' => $reserva_id]);
        $totalParticipantes = $stmtParticipantes->fetch(PDO::FETCH_ASSOC)['total_participantes'];
        

        
        if ($numPagamentos >= $totalNecessario) {
            
            $stmtReserva = $pdo->prepare("
                UPDATE reservas 
                SET status = 'confirmada' 
                WHERE id = :reserva_id
            ");
            $stmtReserva->execute(['reserva_id' => $reserva_id]);
        }
       
    }

    $pdo->commit();

    echo "<script>
        alert('Pagamento processado com sucesso!');
        window.location.href = 'sessao.php?reserva_id={$reserva_id}&status=pago';
    </script>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Erro ao processar pagamento: " . $e->getMessage();
}

?>