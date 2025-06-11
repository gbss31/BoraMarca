<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function enviarEmail($destinatario, $assunto, $mensagemcorpo){
$mail = new PHPMailer(true);

try {

    $mail -> isSMTP();
    $mail -> Host = 'smtp.gmail.com';
    $mail -> SMTPAuth = true;
    $mail -> Username = 'boramarca31@gmail.com';
    $mail -> Password = 'jojr tvao vuhf sckd';
    $mail -> SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail -> Port = 587;

    $mail -> setFrom('boramarca31@gmail.com', 'Boramarca');

    $mail -> addAddress($destinatario);

    $mail -> isHTML(true);
    $mail -> Subject = $assunto;
    $mail -> Body = $mensagemcorpo;
    $mail -> AltBody = strip_tags($mensagemcorpo);
    

    $mail -> send();
    $message = "Email enviado com sucesso!";
    $sucesso = true;

} catch (Exception  $e) {
    $message = "Erro ao enviar email";
    $sucesso = false;
}

}

?>