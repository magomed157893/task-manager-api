<?php

namespace App\Utils;

class Response
{
    public static function send(array $content, int $statusCode = 200, int $jsonFlags = JSON_UNESCAPED_UNICODE)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($content, $jsonFlags);
        exit;
    }
}
