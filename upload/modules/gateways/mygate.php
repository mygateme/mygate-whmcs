<?php
/*
 * ==========================================================
 * MYGATE WHMCS MODULE
 * ==========================================================
 *
 * © 2024 MyGate. All rights reserved.
 *
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function mygate_MetaData() {
    return array(
        'DisplayName' => 'MyGate',
        'APIVersion' => '1.2',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function mygate_config() {
    global $CONFIG;
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'MyGate',
        ),
        'webhookURL' => array(
            'FriendlyName' => 'Webhook URL',
            'Type' => 'text',
            'Size' => '25',
            'Value' => $CONFIG['SystemURL'] . '/modules/gateways/callback/mygate.php',
            'Default' => $CONFIG['SystemURL'] . '/modules/gateways/callback/mygate.php',
            'Description' => 'Copy the webhook URL and paste it into MyGate > Settings > Webhook > Webhook URL.'
        ),
        'mygateKey' => array(
            'FriendlyName' => 'Webhook secret key',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter the MyGate webhook secret key. Get it from MyGate > Settings > Webhook > Webhook secret key.',
        ),
        'cloudKey' => array(
            'FriendlyName' => 'API key',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter the MyGate API key. Get it from MyGate > Account > API key.',
        )
    );
}

function mygate_link($params) {
    $encrypted = localAPI('EncryptPassword', array('password2' => strval($params['invoiceid'])));
    $link = ('https://app.mygate.me/pay.php?checkout_id=custom-whmcs&price=' . $params['amount'] . '&currency=' . $params['currency'] . '&external-reference=' . base64_encode(is_string($encrypted) ? $encrypted : $encrypted['password']) . '&redirect=' . urlencode($params['returnurl']) . (trim($params['cloudKey']) ? '&cloud=' . trim($params['cloudKey']) : '') . '&note=' . urlencode('WHMCS invoice ID ' . $params['invoiceid']));
    return strpos($_SERVER['REQUEST_URI'], 'viewinvoice') ? '<a href="' . $link . '" class="btn btn-success btn-sm" id="mygate-link">' . (!isset($_LANG) || empty($_LANG['PAY_NOW']) ? 'Pay now' : $_LANG['PAY_NOW']) . '</a>' : '<script>document.location = "' . $link . '";</script>';
}