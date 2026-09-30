<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $pdo = null;

    public static function connect() {
        if (self::$pdo === null) {
            $host    = getenv('DB_HOST') ?: 'localhost';
            $port    = getenv('DB_PORT') ?: '3306';
            $db      = getenv('DB_NAME') ?: 'exam_db';
            $user    = getenv('DB_USER') ?: 'root';
            $pass    = getenv('DB_PASS') ?: 'kptm123';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

            try {
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
                exit;
            }
        }
        return self::$pdo;
    }
}