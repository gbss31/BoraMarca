<?php

session_start();
include 'config.php';
include 'enviar_email.php';



if (!isset($_SESSION['nome'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $quadra_id = $_POST['quadra_id'] ?? null;
    $data = $_POST['data'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $duracao = $_POST['duracao'] ?? '';
    $tipo_pagamento = $_POST['tipo_pagamento'];
    

    if (!$quadra_id || empty($data) || empty($hora) || empty($duracao)) {
        echo "Preencha todos os campos corretamente.";
        exit;
    }

    try {
       
        $hora_inicio = $hora;
        $hora_fim = date('H:i:s', strtotime("+$duracao hours", strtotime($hora)));


        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE quadra_id = ? AND data_reserva = ? AND (hora <= ? AND ADDTIME(hora, SEC_TO_TIME(duracao * 3600)) > ?)");
        $stmtCheck->execute([$quadra_id, $data, $hora_fim, $hora_inicio]);
        $reservasExistentes = $stmtCheck->fetchColumn();

        if ($reservasExistentes > 0) {
           
            header("Location: quadra.php?id=$quadra_id&reserva=erro");
            exit;
        }

      
        $stmt = $pdo->prepare("INSERT INTO reservas (usuario_id, quadra_id, data_reserva, hora, duracao, tipo_pagamento) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['id'],
            $quadra_id,
            $data,
            $hora,
            $duracao,
            $tipo_pagamento 
        ]);

        
        
        $stmtUser = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
        $stmtUser->execute([$_SESSION['id']]);
        $usuario = $stmtUser->fetch();
        $nome = $usuario['nome'];
        $email = $usuario['email'];

        
        $stmtQuadra = $pdo->prepare("SELECT nome FROM quadras WHERE id = ?");
        $stmtQuadra->execute([$quadra_id]);
        $dadosQuadra = $stmtQuadra->fetch();
        $quadra = $dadosQuadra['nome'];



        $assunto = "Quadra reservada com sucesso - Boramarca";
        $mensagemcorpo = "
            <h2> Ola, $nome! </h2>
            <p> Sua reserva na quadra: $quadra foi um sucesso.</p>
            <p> Siga detalhes da sua reserva: </p>
            <ul> 
                <li><strong>Data:</strong> $data </li>
                <li><strong>Horario:</strong> $hora </li>
                <li><strong>Quadra:</strong> $quadra </li> 
            </ul>
            <p> Ou acompanhe pelo link abaixo: </p>
            <p><a href='http://localhost/soccer/minhas_reservas.php' target='_blank'> Minhas reservas </a></p>
            <p> Obrigado por usar BoraMarca! <3 </p>";

            enviarEmail($email, $assunto, $mensagemcorpo);
        header("Location: quadra.php?id=$quadra_id&reserva=sucesso");
        exit;
        
    } catch (PDOException $e) {
        echo "Erro ao realizar reserva: " . $e->getMessage();
        exit;
    }
} else {
    echo "Acesso inválido.";
    exit;
}
