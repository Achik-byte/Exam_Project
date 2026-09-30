<?php
namespace App\Middleware;

class LoggerMiddleware {
    // Log setiap request ke fail log
    public static function log($endpoint, $method, $userId = null) {
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) mkdir($logDir, 0777, true);

        $logFile = $logDir . 'api_' . date('Y-m-d') . '.log';
        $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $time    = date('Y-m-d H:i:s');

        $line = "[$time] IP: $ip | Method: $method | Endpoint: $endpoint | User ID: " . ($userId ?? 'guest') . PHP_EOL;
        file_put_contents($logFile, $line, FILE_APPEND);
    }
}