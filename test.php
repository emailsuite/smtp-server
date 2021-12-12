<?php

include __DIR__ . "/SocketManager.php";

$sm = new SocketManager();

$toEmail = 'recipient@example.com';
//$domain = explode('@', $email)[1];
//
//$mxHosts = [];
//getmxrr($domain, $mxHosts);
//$host = $mxHosts[0];
$host = 'mail.smtpbucket.com';
$port = 8025;
$fromEmail = 'sender@example.com';

$socketId = $sm->openSocket($host, $port);
echo $sm->sendMessage($socketId, "helo $host");
echo $sm->sendMessage($socketId, "mail from:<$fromEmail>");
echo $sm->sendMessage($socketId, "rcpt to:<$toEmail>");
echo $sm->sendMessage($socketId, "data");
echo $sm->sendMessage($socketId, "some test email \n.");
