<?php 

require __DIR__ . '/vendor/autoload.php';
require 'config.php';

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

MercadoPagoConfig::setAccessToken('APP_USR-2372243075336974-102119-eec60d9d7908ce31e56259aeb683af36-2939896670');

$client = new PreferenceClient();

$raw = file_get_content('php://input');

if (!$raw) {

    http_response_code(400);
    exit;
}

$data - json_encode($raw, true);

$payment_id = null;

if (!empty($data['data']['id'])) {

    $payment_id = $data['data']['id'];
    
}  else if (!empty($data['data']['id'])) {

    $payment_id = $_GET['id'];
}

if (!$payment_id) {
    http_response_code (400);
    exit;
}

$payment = MercadoPago\Payment::find_by_id($payment_id);

$status = $payment -> status;

$external_ref = $payment -> external_reference ?? null;

if ($status === 'approved') {

    $reservaId = null;

    if (!empty($payment -> external_reference)) {

        $reservaId = intVal($payment -> external_reference);
    } else {

    }

    if ($reservaId) {

        $stmt = $pdo -> prepare ("INSERT INTO pagamentos (reserva_id, payment_id, status, valor, data) VALUES (?, ?, ?, ?, NOW())");
        $stmt -> execute  ([$reservaId, $payment_id, $status, $payment -> transaction_amount]);

        $pdo -> prepare("UPDATE reservas SET paga = 1 WHERE id = ?");
        $stmt -> execute ([$reservaId]);
    }
}

http_response_code(200);
echo "Ok";
