<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $pdo = null;

    public static function connect() {
        if (self::$pdo === null) {
            $host    = 'localhost';
            $db      = 'exam_db';
            $user    = 'root';
            $pass    = 'kptm123';      // Laragon default kosong
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

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