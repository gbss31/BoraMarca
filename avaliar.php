<?php 

session_start();
include 'config.php';


$mensagem = "";
$sucesso = true;


if(!isset($_SESSION['id'])) {

    header('location: login.php');
    exit;
}

$usuario_id = $_SESSION['id'];

$reserva_id = $_GET['reserva_id'] ?? null;

if (!$reserva_id) {

    header('Location: historico.php');
    exit;
}

$sql = "SELECT r.*, q.nome AS nome_quadra
        FROM reservas r
        JOIN quadras q ON r.quadra_id = q.id
        WHERE r.id = :reserva_id
            AND (r.usuario_id = :usuario_id
                OR r.id IN (SELECT reserva_id FROM participantes WHERE usuario_id = :usuario_id))";

$stmt = $pdo -> prepare($sql);
$stmt -> execute(['reserva_id' => $reserva_id, 'usuario_id' => $usuario_id]);
$reserva = $stmt -> fetch(PDO::FETCH_ASSOC);

if (!$reserva) {

    header('Location: historico.php');
    exit;
}

$stmt = $pdo -> prepare("SELECT COUNT(*) FROM avaliacoes WHERE reserva_id = ?");
$stmt -> execute([$reserva_id]);
$jaAvaliada = $stmt -> fetchColumn() > 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$jaAvaliada) {

    $nota = (int) $_POST['nota'];
    $comentario = $_POST['comentario'];

    if($nota < 1 || $nota > 5) {

       $mensagem = "Nota invalida, selecione entre 1 a 5";
       $sucesso = false;

    } else {

        $sql = "INSERT INTO avaliacoes (reserva_id, usuario_id, quadra_id, nota, comentario, data_avaliacao)
        VALUES(:reserva_id, :usuario_id, :quadra_id, :nota, :comentario, NOW())";

        $stmt = $pdo -> prepare($sql);
        $stmt -> execute ([
            'reserva_id' => $reserva_id,
            'usuario_id' => $usuario_id,
            'quadra_id' => $reserva['quadra_id'],
            'nota' => $nota,
            'comentario' => $comentario
        ]);


        $mensagem = "Avaliação enviada com sucesso!";
        $sucesso = true;
        header("refresh:3;url=index.php");

    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">
    <title>Avaliar quadra</title>
    <link rel="stylesheet" href="css/avaliar.css">
    <style>

         form {
            margin-top: 20px;
        }
        textarea {
            width: 100%;
            height: 100px;
            resize: none;
        }
        button {
            margin-top: 10px;
            background-color: #201d39f2;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #15132a;
        }

    </style>

</head>
<body>
<header>

<h1> Avaliar quadra </h1>
<a href="historico.php"> Voltar </a>


</header>

<main>

<div class="quadra-card">

    <p><strong> Quadra: </strong><?= htmlspecialchars($reserva['nome_quadra']) ?></p>
    <p><strong> Data: </strong><?= htmlspecialchars($reserva['data_reserva']) ?></p>
    <p><strong> Hora: </strong><?= htmlspecialchars($reserva['hora']) ?></p>

</div>

<?php if ($jaAvaliada): ?>

<p> Voce já avaliou esta quadra! </p>


<?php else: ?>

<div class="form-container">
    <form method="POST">
        <label for="nota"> Nota (1 a 5): </label>
        <select name="nota" id="nota" required>
            <option value="1"> 1 - Pessima </option>
            <option value="2"> 2 - Ruim </option>
            <option value="3"> 3 - Regular </option>
            <option value="4"> 4 - Boa </option>
            <option value="5"> 5 - Excelente </option>
        </select>

        <br><br>

        <label for="comentario"> Comentario: </label>
        <textarea name="comentario" id="comentario" placeholder="Deixe seu comentario aqui(opcional)"> </textarea>

        <br>

        <button type="submit"> Enviar avaliação </button>

    </form>

</div>

<?php endif; ?>

    <div id="toast" class="toast"></div>

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
</body>

</html>