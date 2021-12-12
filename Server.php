<?php

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Nyholm\Psr7\Response;

class Server
{
    private $socketManager;

    public function __construct()
    {
        $this->socketManager = new \SocketManager();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getBody()->getContents();
        $body = json_decode($body, true);
        if (!$body['action']) {
            return new Response(404, [],'Not found');
        }

        if ($body['action'] == 'init') {
            $socketId = $this->socketManager->openSocket($body['host'], $body['port'], $body['timeout']);
        } else {
            $socketId = $body['socket_id'];
        }
        if ($body['action'] == 'close') {
            $this->socketManager->closeSocket($socketId);
            return new Response();
        }
        list($code, $response) = $this->socketManager->sendMessage($socketId, $body['message']);
        $result = [
            'socket_id' => $socketId,
            'code' => $code,
            'response' => $response,
        ];
        return new Response(200, [], json_encode($result));
    }
}