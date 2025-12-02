        <?php 

        session_start();

        include 'config.php';
        include 'enviar_email.php';

        if (!isset($_SESSION['id'])){

            header("Location: login.php");
            exit;
        }

        $reserva_id = $_GET['reserva_id'];
        $usuario_id = $_SESSION['id'];

        $reserva = $pdo -> prepare ("SELECT r.*, u.nome AS nome_usuario, q.nome AS nome_quadra, q.preco_hora FROM reservas r 
                                            JOIN usuarios u ON  r.usuario_id = u.id
                                            JOIN quadras q ON r.quadra_id = q.id
                                            WHERE r.id = :id");

        $reserva -> execute(['id' => $reserva_id]);
        $reserva_dados = $reserva -> fetch(PDO::FETCH_ASSOC);


        $mensagens = $pdo -> prepare ("SELECT m.* , u.nome AS nome_usuario FROM chat_mensagens m JOIN usuarios u ON m.usuario_id = u.id
                                    WHERE m.reserva_id = :reserva_id ORDER BY m.data_envio ASC");
        $mensagens -> execute(['reserva_id' => $reserva_id]);

        $participantes_dados = [];

        $stmtPagos = $pdo -> prepare (
            "SELECT usuario_id 
            FROM pagamentos
            WHERE reserva_id = :reserva_id AND status = 'pago'");
        $stmtPagos -> execute (['reserva_id' => $reserva_id]);
        $pagamentosFeitos = $stmtPagos -> fetchAll(PDO::FETCH_COLUMN, 0);
    
        $stmtParticipantes = $pdo -> prepare (
            "SELECT usuario_id 
            FROM participantes 
            WHERE reserva_id = :reserva_id");
        $stmtParticipantes -> execute (['reserva_id' => $reserva_id]);
        $participantesExtras = $stmtParticipantes -> fetchAll (PDO::FETCH_COLUMN,0);

        $listaFinal = array_unique( array_merge([$reserva_dados['usuario_id']], $participantesExtras));


        foreach ($listaFinal as $id) {

            $stmtUser = $pdo -> prepare ("SELECT nome FROM usuarios WHERE id = :id");
            $stmtUser -> execute (['id' => $id]);
            $nome = $stmtUser -> fetch (PDO::FETCH_ASSOC)['nome'];
            
            $status_pago = in_array($id, $pagamentosFeitos);

            $participantes_dados[] = [
                'id' => $id,
                'nome' => $nome,
                'pago' => $status_pago
            ];

        }

        ?>

        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>

            <meta charset="UTF-8">
            <title>Sessão</title>
            <link rel="stylesheet" href="css/sessao.css">

        </head>
        <body>

        <a class="voltar" href="minhas_reservas.php">← Voltar</a>

        <div class="sessao-container">
    <!-- Coluna da esquerda: chat -->
    <div class="chat-container">
        <h2>Chat da sessão</h2>
        <div id="mensagens">
            <?php while ($msg = $mensagens->fetch(PDO::FETCH_ASSOC)): ?>
                <?php 
                    $classe = ($msg['usuario_id'] == $_SESSION['id']) ? 'usuario' : 'outro';
                ?>
                <div class="mensagem <?= $classe ?>">
                    <p><strong><?= htmlspecialchars($msg['nome_usuario']) ?>:</strong> <?= htmlspecialchars($msg['mensagem']) ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <form id="formChat" method="POST" action="enviar_mensagem.php">
            <input type="hidden" name="reserva_id" value="<?= $reserva_id ?>">
            <input type="text" name="mensagem" id="mensagem" placeholder="Digite sua mensagem..." required>
            <button type="submit">Enviar</button>
        </form>
    </div>


    <?php 

    $sqlParticipantes = "SELECT COUNT(*) AS total FROM participantes WHERE reserva_id = ?";
    $stmtParticipantes = $pdo -> prepare ($sqlParticipantes);
    $stmtParticipantes -> execute ([$reserva_dados['id']]);

    $totalParticipantes = $stmtParticipantes -> fetch(PDO::FETCH_ASSOC)['total'];

    $pessoasNaSessao = $totalParticipantes + 1;

    $valorTotal = $reserva_dados['preco_hora'] * $reserva_dados['duracao'];

    $valorPorPessoa = ($pessoasNaSessao > 0) ? $valorTotal / $pessoasNaSessao : 0;

    $valorPorPessoa = round((float)$valorPorPessoa, 2);

    

    ?>

    <!-- Coluna da direita: informações -->
    <div class="info-container">
        <div class="informacoes-reserva">
            <h3>Informações da reserva</h3>
            <p>Local: <?= htmlspecialchars($reserva_dados['nome_quadra']) ?></p>
            <p>Data: <?= htmlspecialchars($reserva_dados['data_reserva']) ?></p>
            <p>Horario: <?= htmlspecialchars($reserva_dados['hora']) ?></p>
            <p>Duração: <?= htmlspecialchars($reserva_dados['duracao']) ?>h</p>
            <p>Valor total: <?= number_format($valorTotal, 2, ',', '.') ?> </p>
            <?php if ($reserva_dados['tipo_pagamento'] === 'dividido'): ?>
            <p>Valor por pessoa: <?= number_format($valorPorPessoa, 2,',','.') ?> </p>
            <?php endif; ?>        </div>
        <div class="participantes">
            <h3>Participantes</h3>
            <ul>
                <?php foreach ($participantes_dados as $p): ?>
                    <li>
                        <?= htmlspecialchars($p['nome']) ?>
                        <?php if ($p['pago']): ?>
                            <span class="pago-status"> PAGO ✅ </span>
                        <?php endif; ?>

                        <?php if($p['id'] == $reserva_dados['usuario_id']): ?>
                            <small> Criador </small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="botoes-acoes">
         <form action="gerar_link.php" method="POST" >
                        <input type="hidden" name="reserva_id" value="<?= $reserva_dados['id'] ?>">
                        <button type="submit">Gerar Convite</button>
        </form>

         <form action="desmarcar_reserva.php" method="POST">
                        <input type="hidden" name="reserva_id" value="<?= $reserva_dados['id'] ?>">
                        <button type="submit">Desmarcar</button>
        </form>

        <?php 
        $usuario_atual_pago = false;
        foreach ($participantes_dados as $p){
            if ($p['id'] == $usuario_id) {
                $usuario_atual_pago = $p['pago'];
                break;  
            }
        } 
        
        $valorPagar =($reserva_dados['tipo_pagamento'] == 'dividido') ? $valorPorPessoa : $valorTotal;
        $labelPagar = 'Pagar R$' . number_format($valorPagar, 2, ',', '.');

        ?>

        <?php if($reserva_dados['tipo_pagamento'] == 'dividido' || $reserva_dados['usuario_id'] == $_SESSION['id']): ?>
            <?php if ($usuario_atual_pago): ?>
                <button type="button" class="btn-pago"> PAGO ✅ </button>
            <?php else: ?>
        <form action="pagamento.php" method="POST">
               <input type="hidden" name="reserva_id" value="<?= htmlspecialchars($reserva_dados['id']) ?>">
               <input type="hidden" name="valor_por_pessoa" value="<?= $valorPorPessoa ?>">
            <button type="submit"> <?= $labelPagar ?> </button>
            </form>

        <?php endif; ?>

        <?php elseif ($reserva_dados['tipo_pagamento'] === 'sozinho' && $reserva_dados['usuario_id'] == $_SESSION['id']): ?>
            <form action="pagamento.php" method="POST">
               <input type="hidden" name="reserva_id" value="<?= htmlspecialchars($reserva_dados['id']) ?>">
               <input type="hidden" name="valor_por_pessoa" value="<?= $valorTotal ?>">
            <button type="submit"> Pagar R$ <?= number_format($valorTotal,2,',','.') ?> </button>
        </form>

        <?php endif; ?>
        </div>

    </div>
</div>

            </body>

            <script>

                
                const form = document.getElementById('formChat');
                const mensagensDiv = document.getElementById('mensagens');
                

                form.addEventListener('submit', e => {
                    e.preventDefault();

                const dados = new FormData(form);

                    fetch('enviar_mensagem.php', {
                        method: 'POST', 
                        body: dados
                    })

                    .then (() => {

                    document.getElementById('mensagem').value ='';
                    atualizarMensagens();

                    })

                    .catch(err => console.error('Erro ao enviar mensagem:', err));

                });

function atualizarMensagens() {
    fetch('buscar_mensagens.php?reserva_id=<?= $reserva_id ?>')
        .then(res => res.json()) // transforma em JSON
        .then(mensagens => {
            mensagensDiv.innerHTML = '';
            mensagens.forEach(msg => {
                const div = document.createElement('div');
                const classe = (msg.usuario_id == <?= $_SESSION['id'] ?>) ? 'mensagem usuario' : 'mensagem outro';
                div.className = classe;
                div.innerHTML = `<p><strong>${msg.nome_usuario}:</strong> ${msg.mensagem}</p>`;
                mensagensDiv.appendChild(div);
            });
            mensagensDiv.scrollTop = mensagensDiv.scrollHeight;
        })
        .catch(err => console.error('Erro ao buscar mensagens:', err));
}

                
                mensagensDiv.scrollTop = mensagensDiv.scrollHeight;
                setInterval(atualizarMensagens,2000);
                atualizarMensagens();
                mensagensDiv.scrollTop = mensagensDiv.scrollHeight;
            

            </script>
            
        </body>
        </html> 