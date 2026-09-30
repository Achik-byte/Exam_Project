<?php
namespace App\Middleware;

use App\Helpers\Response;

class RateLimitMiddleware {
    // Simple rate limit guna file-based storage
    public static function check($maxRequests = 60, $windowSeconds = 60) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $dir = sys_get_temp_dir() . '/exam_api_rate/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $file = $dir . md5($ip) . '.json';
        $now  = time();

        $data = ['count' => 0, 'start' => $now];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: $data;
            if ($now - $data['start'] > $windowSeconds) {
                $data = ['count' => 0, 'start' => $now];
            }
        }

        $data['count']++;
        file_put_contents($file, json_encode($data));

        header('X-RateLimit-Limit: ' . $maxRequests);
        header('X-RateLimit-Remaining: ' . max(0, $maxRequests - $data['count']));

        if ($data['count'] > $maxRequests) {
            header('Retry-After: ' . ($windowSeconds - ($now - $data['start'])));
            Response::error('Rate limit exceeded. Try again later.', 429);
        }
    }
}