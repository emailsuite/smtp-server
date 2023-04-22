<?php

require_once __DIR__ . "/vendor/autoload.php";

use \PHPMailer\PHPMailer\PHPMailer;

$email = "someTestMail@gmail.com";
$result = send($email);
var_dump($result);

function send($toEmail)
{
    $timeStart = microtime(true);
    $domain = explode('@', $toEmail)[1];
    $mxHosts = [];
    $subject = 'Test subject';
    $body = 'Test body';
    getmxrr($domain, $mxHosts);
    if (!count($mxHosts)) {
        return 'no mx';
    }
    $host = $mxHosts[0];
    $port = 25;
    $fromEmail = 'noreply@sender.com';
    $fromName = 'Test Sender';
    $fromDomain = explode('@', $fromEmail)[1];

    $mail = new PHPMailer(true);
    $mail->setFrom($fromEmail, $fromName);
    $mail->DKIM_domain = $fromDomain;
    $mail->DKIM_private_string = "-----BEGIN RSA PRIVATE KEY-----\nMIICXAIB(GENERATE AND ADD YOUR OWN)\n-----END RSA PRIVATE KEY-----";
    $mail->DKIM_selector = 'own';
    $mail->DKIM_identity = $fromEmail;
    $mail->isSMTP();
    $mail->MessageID = "<" . md5(uniqid()) . "@$fromDomain>";
    $mail->addAddress($toEmail);
    $mail->XMailer = ' ';
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->preSend();
    $mimeMessage = $mail->getSentMIMEMessage();
    //echo $mimeMessage;die;


    echo 'init ';
    $result = sendMessage([
        'action' => 'init',
        'host' => $host,
        'port' => $port,
    ]);
    echo json_encode($result) . "\n";
    $socketId = $result['socket_id'];
    $result['code'] = $result['code'] ?? null;
    if ($result['code'] >= 400 || $result['http_code'] >= 300) {
        return;
        //return $result['response'];
    }

    echo 'helo ';
    $result = sendMessage([
        'action' => 'message',
        'socket_id' => $socketId,
        'message' => "helo $host",
    ]);
    echo json_encode($result) . "\n";
    if ($result['code'] >= 400 || $result['http_code'] >= 300) {
        return $result['response'];
    }

    //echo 'check tls ';
    //$result = sendMessage([
    //    'action' => 'message',
    //    'socket_id' => $socketId,
    //    'message' => "STARTTLS",
    //]);
    //echo json_encode($result) . "\n";
    //if ($result['http_code'] >= 300) {
    //    return $result['response'];
    //}
    //
    //if ($result['code'] == 220) { // Ready for TLS
    //    echo 'enable tls ';
    //    $result = sendMessage([
    //        'action' => 'tls',
    //        'socket_id' => $socketId,
    //    ]);
    //    echo json_encode($result) . "\n";
    //    if ($result['http_code'] >= 300) { // @TODO try again
    //        return 'tls broken';
    //    }
    //
    //    echo 'helo ';
    //    $result = sendMessage([
    //        'action' => 'message',
    //        'socket_id' => $socketId,
    //        'message' => "helo $host",
    //    ]);
    //    echo json_encode($result) . "\n";
    //    if ($result['code'] >= 400 || $result['http_code'] >= 300) {
    //        return $result['response'];
    //    }
    //}

    echo 'from ';
    $result = sendMessage([
        'action' => 'message',
        'socket_id' => $socketId,
        'message' => "mail from: <$fromEmail>",
    ]);
    echo json_encode($result) . "\n";
    if ($result['code'] >= 400 || $result['http_code'] >= 300) {
        return $result['response'];
    }

    echo 'rcpt ';
    $result = sendMessage([
        'action' => 'message',
        'socket_id' => $socketId,
        'message' => "rcpt to:<$toEmail>",
    ]);
    echo json_encode($result) . "\n";
    if ($result['code'] >= 400 || $result['http_code'] >= 300) {
        return $result['response'];
    }
    return true;

    echo 'data ';
    $result = sendMessage([
        'action' => 'message',
        'socket_id' => $socketId,
        'message' => "data",
    ]);
    echo json_encode($result) . "\n";
    if ($result['code'] >= 400 || $result['http_code'] >= 300) {
        return $result['response'];
    }

    echo 'message ';
    $result = sendMessage([
        'action' => 'message',
        'socket_id' => $socketId,
        'message' => $mimeMessage . "\r\n.",
    ]);
    echo json_encode($result) . "\n";
    //if ($result['code'] >= 400 || $result['http_code'] >= 300) { @TODO write result on message
    //    return $result['response'];
    //}

    // TODO quit

    echo 'Done in ' . round((microtime(true) - $timeStart) * 1000) . "ms \n";
    return true;
}


function sendMessage($postData = []): array
{
    $ch = curl_init();
    $postData['token'] = 'auuwechvw_test_token_nv87';
    curl_setopt($ch, CURLOPT_URL, "http://mail_server_ip_or_domain:8080");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    if (!$data) {
        $data = ['not_json' => $response];
    }
    $data['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($curlError = curl_error($ch)) {
        $data['curl_error'] = $curlError;
        $data['http_code'] = 500;
    }
    return $data;
}


