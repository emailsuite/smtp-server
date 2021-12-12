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

$socketId = $sm->openSocket($host, $port);
list($code, $message) = $sm->sendMessage($socketId, "helo $host");
echo $message;
if ($code > 300) {
    die;
}
list($code, $message) =  $sm->sendMessage($socketId, "mail from:<$fromEmail>");
echo $message;
if ($code > 300) {
    die;
}
list($code, $message) =  $sm->sendMessage($socketId, "rcpt to:<$toEmail>");
echo $message;
if ($code > 300) {
    die;
}
list($code, $message) =  $sm->sendMessage($socketId, "data");
echo $message;
if ($code > 300) {
    die;
}
list($code, $message) =  $sm->sendMessage($socketId, "subject: My Telnet Test Email \n some test email \r\n.");
echo $message;
if ($code > 300) {
    die;
}
