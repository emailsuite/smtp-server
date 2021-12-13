<?php

include __DIR__ . "/SocketManager.php";

$sm = new SocketManager();

$toEmail = 'test@gmail.com';
$domain = explode('@', $toEmail)[1];
$mxHosts = [];
getmxrr($domain, $mxHosts);
$host = $mxHosts[0];
$port = 25;
$fromEmail = 'sender@example.com';


$ch = curl_init();
function sendMessage($ch, $data = []): array
{
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8080");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    if (!$data) {
        $data = ['not_json' => $response];
    }
    return $data;
}


$result = sendMessage($ch, [
    'action' => 'init',
    'host' => $host,
    'port' => $port,
]);
echo json_encode($result) . "\n";
$socketId = $result['socket_id'];
if ($result['code'] >= 400) {
    die;
}

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "helo $host",
]);
echo json_encode($result) . "\n";
if ($result['code'] >= 400) {
    die;
}

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "mail from:<$fromEmail>",
]);
echo json_encode($result) . "\n";
if ($result['code'] >= 400) {
    die;
}

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "rcpt to:<$toEmail>",
]);
echo json_encode($result) . "\n";
if ($result['code'] >= 400) {
    die;
}

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "data",
]);
echo json_encode($result) . "\n";
if ($result['code'] >= 400) {
    die;
}

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "subject: My Telnet Test Email \n some test email \r\n.",
]);
echo json_encode($result) . "\n";
if ($result['code'] >= 400) {
    die;
}
