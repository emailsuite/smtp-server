<?php

# getting binary: ../vendor/bin/rr get-binary
# run command:  ./rr serve

use Spiral\RoadRunner;
use Nyholm\Psr7;

include __DIR__ . "/../vendor/autoload.php";
include __DIR__ . "/../Server.php";
include __DIR__ . "/../SocketManager.php";


$worker = RoadRunner\Worker::create();
$psrFactory = new Psr7\Factory\Psr17Factory();

$worker = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

register_shutdown_function(function () use ($worker) {
    $message = ob_get_contents(); // Capture 'Doh'
    ob_end_clean(); // Cleans output buffer
    $response = new Psr7\Response(500, [], 'Server error with text: ' . json_encode($message));
    $worker->respond($response);
});


$server = new Server();
while ($request = $worker->waitRequest()) {
    // magic fix for json body
    $request->getBody()->rewind();
    try {
        $response = $server->handle($request);
        $worker->respond($response);
    } catch (\Throwable $e) {
        $response = new Psr7\Response(500, [], json_encode(['server_error' => $e->getMessage()]));
        $worker->respond($response);
        //$worker->getWorker()->error($e->getMessage());
    }
}