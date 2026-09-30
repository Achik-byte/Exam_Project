<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Helpers\Response;

class AuthMiddleware {
    private static $secretKey = 'EXAM_API_SECRET_KEY_2026_SWC3633';

    // Cipta Token JWT
    public static function generateToken($user) {
        $payload = [
            'iss'       => 'exam_api',
            'iat'       => time(),
            'exp'       => time() + (60 * 60 * 2), // 2 jam
            'user_id'   => $user['user_id'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'full_name' => $user['full_name'],
        ];
        return JWT::encode($payload, self::$secretKey, 'HS256');
    }

    // Sahkan Token dari Header Authorization
    public static function verifyToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            Response::error('Authorization token missing', 401);
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            Response::error('Invalid token format. Use: Bearer <token>', 401);
        }

        try {
            $decoded = JWT::decode($parts[1], new Key(self::$secretKey, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            Response::error('Invalid or expired token: ' . $e->getMessage(), 401);
        }
    }

    // Semak Peranan (Role-Based Access Control)
    public static function requireRole($allowedRoles) {
        $user = self::verifyToken();
        if (!in_array($user['role'], $allowedRoles)) {
            Response::error('Access denied. Required role: ' . implode(', ', $allowedRoles), 403);
        }
        return $user;
    }
}