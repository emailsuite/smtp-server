<?php

# run command:  ./roadrunner/rr serve

use Spiral\RoadRunner;
use Nyholm\Psr7;

include "../vendor/autoload.php";

$worker = RoadRunner\Worker::create();
$psrFactory = new Psr7\Factory\Psr17Factory();

$worker = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

register_shutdown_function(function () use ($worker) {
    $message = ob_get_contents(); // Capture 'Doh'
    ob_end_clean(); // Cleans output buffer
    $response = new Psr7\Response(500, [], 'Server error with text: ' . json_encode($message));
    $worker->respond($response);
});


while ($request = $worker->waitRequest()) {
    // magic fix for json body
    $request->getBody()->rewind();

    //$timeStart = microtime(true);
    try {
        $response = $apiWorker->handleRequest($request);
        $worker->respond($response);
    } catch (\Throwable $e) {
        $response = new Psr7\Response(500, [], 'Server error: ' . $e->getMessage());
        $worker->respond($response);
        //$worker->getWorker()->error($e->getMessage());
    }

    //$timeDone = round((microtime(true) - $timeStart) * 1000);
    //$response = $response->withAddedHeader('X-Execution-Time', $timeDone);
}