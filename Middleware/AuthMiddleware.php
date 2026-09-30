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

    public static function verifyToken() {
    // Cuba pelbagai cara untuk ambil Authorization header
    $authHeader = null;

    // Cara 1: $_SERVER (paling reliable dalam Docker)
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    // Cara 2: REDIRECT_HTTP_AUTHORIZATION (selepas rewrite)
    elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    // Cara 3: getallheaders() (fallback)
    else {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    }

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