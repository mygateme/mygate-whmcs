<?php
/*
 * ==========================================================
 * MYGATE WHMCS MODULE - CALLBACK FILE
 * ==========================================================
 *
 * © 2024 MyGate. All rights reserved.
 *
 */

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);
if (!$gatewayParams['type']) die('Module Not Activated');
$response = json_decode(file_get_contents('php://input'), true);
$transaction = $response['transaction'];
$invoice_id = localAPI('DecryptPassword', array('password2' => strval(base64_decode($transaction['external_reference']))))['password'];
if (!is_string($invoice_id)) $invoice_id = $invoice_id['password'];
$invoice = localAPI('GetInvoice', array('invoiceid' => $invoice_id));
if (trim($response['key']) !== trim($gatewayParams['mygateKey'])) debug('Invalid webhook key.');
if (!$invoice || floatval($invoice['total']) > floatval($transaction['amount_fiat'])) debug('[MyGate transaction ID ' . $transaction['id'] . '] Invalid amount. Invoice: ' . ($invoice ? json_encode($invoice) : 'no') . '. MyGate transaction amount: ' . $transaction['amount_fiat'] . '. Decrypted invoice ID: ' . $invoice_id);
$invoice_id = checkCbInvoiceID($invoice_id, $gatewayParams['name']);
checkCbTransID($transaction['id']);
logTransaction($gatewayParams['name'], $_POST, 'success');
addInvoicePayment($invoice_id, $transaction['id'], $transaction['amount_fiat'], 0, $gatewayModuleName);

function debug($value) {
    $value = is_string($value) ? $value : json_encode($value);
    $path = __DIR__ . '/debug.txt';
    if (file_exists($path)) {
        $value = file_get_contents($path) . PHP_EOL . $value;
    }
    $file = fopen($path, 'w');
    fwrite($file, $value);
    fclose($file);
}

?>