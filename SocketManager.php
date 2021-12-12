<?php

class SocketManager
{
    private const CRLF = "\r\n";
    private static $sockets = [];
    private static $socketOpened = [];

    public function openSocket($host, int $port, int $timeout = 1): int
    {
        $socket = fsockopen($host, $port, $errorCode, $errorMessage, $timeout);
        if (!$socket) {
            throw new Exception('Socket opening error: ' . $errorMessage);
        }
        $this->readResponse($socket);
        $socketId = get_resource_id($socket);
        self::$sockets[$socketId] = $socket;
        self::$socketOpened[$socketId] = time();
        return $socketId;
    }

    public function closeSocket($socketId)
    {
        unset(self::$sockets[$socketId]);
        unset(self::$socketOpened[$socketId]);
        fclose($socketId);
    }

    public function sendMessage($socketId, $message): array
    {
        $message = trim($message) . self::CRLF;
        $socket = self::$sockets[$socketId];
        fputs($socket, $message);
        return $this->readResponse($socket);
    }

    private function readResponse($socket): array
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