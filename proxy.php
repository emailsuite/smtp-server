<?php

use Spiral\RoadRunner;
use Nyholm\Psr7;

include __DIR__ ."/vendor/autoload.php";
include __DIR__ ."/Server.php";

$worker = RoadRunner\Worker::create();
$psrFactory = new Psr7\Factory\Psr17Factory();

$worker = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

$server = new Server();

while ($request = $worker->waitRequest()) {
    try {
        $server->handle($request);

        $response = new Psr7\Response();
        $response->getBody()->write('Hello world!');

        $worker->respond($response);
    } catch (\Throwable $e) {
        $worker->getWorker()->error((string)$e);
    }
}




$body = file_get_contents('php://input');
$body = json_decode($body);


$socket = fsockopen('mail.server.com', 80, $errno, $errstr, 30);


$sendMessage = $body['message'];

