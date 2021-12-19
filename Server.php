<?php

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Nyholm\Psr7\Response;

class Server
{
    private $socketManager;
    private $serverId;

    public function __construct()
    {
        $this->serverId = uniqid();
        $this->socketManager = new \SocketManager();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getBody()->getContents();
        $body = json_decode($body, true);

        if (!isset($body['action'])) {
            return new Response(404, [], 'Not found');
        }
        if (!isset($body['token']) || $body['token'] != 'auuwechvw_test_token_nv873ta34v') { //@TODO
            return new Response(401, [], 'Invalid auth token');
        }

        if ($body['action'] == 'init') {
            $socketId = $this->socketManager->openSocket($body['host'], $body['port']);
            return new Response(200, [], json_encode([
                'server_id' => $this->serverId,
                'socket_id' => $socketId,
            ]));
        } else {
            $socketId = $body['socket_id'];
        }
        if ($body['action'] == 'close') {
            $this->socketManager->closeSocket($socketId);
            return new Response();
        }
        list($code, $response) = $this->socketManager->sendMessage($socketId, $body['message']);
        $result = [
            'server_id' => $this->serverId,
            'socket_id' => $socketId,
            'code' => $code,
            'response' => $response,
        ];
        return new Response(200, [], json_encode($result));
    }
}