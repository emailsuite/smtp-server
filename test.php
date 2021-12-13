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
function sendMessage($ch, $data = [])
{
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8080");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    return $response;
    //return json_decode($response, true);
}


$result = sendMessage($ch, [
    'action' => 'init',
    'host' => $host,
    'port' => $port,
]);
echo $result . "\n";
die;
$socketId = 5;

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "helo $host",
]);
echo json_encode($result) . "\n";

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "mail from:<$fromEmail>",
]);
echo json_encode($result) . "\n";

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "rcpt to:<$toEmail>",
]);
echo json_encode($result) . "\n";

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "data",
]);
echo json_encode($result) . "\n";

$result = sendMessage($ch, [
    'action' => 'message',
    'socket_id' => $socketId,
    'message' => "subject: My Telnet Test Email \n some test email \r\n.",
]);
echo json_encode($result) . "\n";
