<?php

namespace App\Database;

use App\Utils\Response;
use PDO;
use PDOException;

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            $config = [
                'host'    => $_ENV['DB_HOST'],
                'port'    => 3306,
                'dbname'  => $_ENV['DB_NAME'],
                'charset' => 'utf8mb4'
            ];
    
            $dsn = 'mysql:' . http_build_query($config, '', ';');
    
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
    
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);  
            } catch (PDOException $e) {
                Response::send(['error' => 'Database connection failed: ' . $e->getMessage()], 500);
            }
        }

        return self::$instance;
    }
}
