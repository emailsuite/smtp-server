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

        if ($body['action'] == 'check_email') {
            return $this->checkEmail($body['email'], $body['host'], $body['port']);
        }

        if ($body['action'] == 'init') {
            list($socketId, $code, $response) = $this->socketManager->openSocket($body['host'], $body['port']);
            return new Response(200, [], json_encode([
                'server_id' => $this->serverId,
                'socket_id' => $socketId,
                'code' => $code,
                'response' => $response,
            ]));
        } else {
            $socketId = $body['socket_id'];
        }
        if ($body['action'] == 'close') {
            $this->socketManager->closeSocket($socketId);
            return new Response();
        }
        if ($body['action'] == 'tls') {
            $result = $this->socketManager->enableTls($socketId);
            if ($result) {
                return new Response(200, [], json_encode(['tls' => $result]));
            } else {
                return new Response(500, [], json_encode(['tls' => $result]));
            }
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

    private function checkEmail($toEmail, $fromEmail, $host, $port)
    {
        list($socketId, $code, $response) = $this->socketManager->openSocket($host, $port);
        list($code, $response) = $this->socketManager->sendMessage($socketId, "helo $host");
        if ($code >= 300) {
            return new Response(200, [], json_encode(['code' => $code, 'response' => $response]));
        }
        list($code, $response) = $this->socketManager->sendMessage($socketId, "mail from: <$fromEmail>");
        if ($code >= 300) {
            return new Response(200, [], json_encode(['code' => $code, 'response' => $response]));
        }
        list($code, $response) = $this->socketManager->sendMessage($socketId, "rcpt to:<$toEmail>");
        $this->socketManager->closeSocket($socketId);
        return new Response(200, [], json_encode(['code' => $code, 'response' => $response]));
    }
}