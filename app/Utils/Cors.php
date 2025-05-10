<?php

namespace App\Utils;

class Cors
{
    public static function handle(string $method)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
