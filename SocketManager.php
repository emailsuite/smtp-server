<?php

class SocketManager
{
    private const CRLF = "\r\n";
    private static $sockets = [];
    private static $socketOpened = [];

    public function openSocket($host, int $port, int $timeout = 1): array
    {
        $socket = fsockopen($host, $port, $errorCode, $errorMessage, $timeout);
        if (!$socket) {
            throw new Exception('Socket opening error: ' . $errorMessage);
        }
        list($code, $response) = $this->readResponseAndCode($socket);
        $socketId = uniqid(get_resource_id($socket) . '-');
        if (isset(self::$sockets[$socketId])) {
            $socketId = uniqid(get_resource_id($socket) . '-', true);
        }
        self::$sockets[$socketId] = $socket;
        self::$socketOpened[$socketId] = time();
        return [$socketId, $code, $response];
    }

    public function closeSocket($socketId)
    {
        unset(self::$sockets[$socketId]);
        unset(self::$socketOpened[$socketId]);
        fclose($socketId);
    }

    public function enableTls($socketId): bool
    {
        return stream_socket_enable_crypto(
            self::$sockets[$socketId],
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT,
        );
    }

    public function sendMessage($socketId, $message): array
    {
        $message = trim($message) . self::CRLF;
        $socket = self::$sockets[$socketId];
        fputs($socket, $message);
        list($code, $response) = $this->readResponseAndCode($socket);
        return [$code, $response];
    }

    private function readResponseAndCode($socket): array
    {
        $code = 0;
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= trim($line) . "\n";
            if (!$code) {
                $code = (int)substr($response, 0, 3);
            }
            if ($line[3] === ' ') {
                break;
            }
        }
        return [$code, $response];
    }
}