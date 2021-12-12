<?php

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Nyholm\Psr7\Response;

class Server
{
    private $sockets = [];
    private $socketOpened = [];

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getBody()->getContents();
        $body = json_decode($body, true);
        if ($body['action'] == 'init') {
            $socketId = $this->createSocket($body['host'], $body['port'], $body['timeout']);
        } else {
            $socketId = $body['socket_id'];
        }
        if ($body['action'] == 'close') {
            $this->closeSocket($socketId);
        }
        $response = $this->sendMessage($socketId, $body['message']);
        $result = [
            'socket_id' => $socketId,
            'response' => $response,
        ];
        return new Response(200, [], json_encode($result));
    }

    private function createSocket($host, int $port, int $timeout = 1): int
    {
        $socket = fsockopen($host, $port, $errorCode, $errorMessage, $timeout);
        $socketId = get_resource_id($socket);
        $this->sockets[$socketId] = $socket;
        $this->socketOpened[$socketId] = time();
    }

    private function closeSocket($socketId)
    {
        unset($this->sockets[$socketId]);
        unset($this->socketOpened[$socketId]);
        fclose($socketId);
    }

    function sendMessage($socketId, $message): string
    {
        $socket = $this->sockets[$socketId];
        fputs($socket, $message);
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= trim($line) . "\n";
            if ($line[3] === ' ') {
                break;
            }
        }
        return $response;
    }
}