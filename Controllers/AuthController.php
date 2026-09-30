<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

class AuthController {

    // POST /login
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email']) || empty($data['no_ic'])) {
            Response::error('Email and IC number are required', 400);
        }

        $pdo  = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();

        if (!$user || $data['no_ic'] !== $user['no_ic']) {
            Response::error('Invalid credentials', 401);
        }

        if (!$user['is_active']) {
            Response::error('Account is inactive', 403);
        }

        $token = AuthMiddleware::generateToken($user);

        Response::success('Login successful', [
            'token'     => $token,
            'user_id'   => $user['user_id'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
        ]);
    }

    // POST /register
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['full_name', 'email', 'no_ic', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("Field '$field' is required", 400);
            }
        }

        if (!in_array($data['role'], ['admin', 'lecturer', 'student'])) {
            Response::error('Invalid role', 400);
        }

        $pdo = Database::connect();

        // Check email wujud
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            Response::error('Email already exists', 409);
        }

        $hashed = password_hash($data['no_ic'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users 
            (full_name, email, password, role, matric_no, no_ic, phone, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $hashed,
            $data['role'],
            $data['matric_no'] ?? null,
            $data['no_ic'],
            $data['phone'] ?? null,
        ]);

        Response::success('User registered successfully', ['user_id' => $pdo->lastInsertId()], 201);
    }

    // GET /profile (perlu token)
    public function profile() {
        $auth = AuthMiddleware::verifyToken();

        $pdo  = Database::connect();
        $stmt = $pdo->prepare("SELECT user_id, full_name, email, role, matric_no, phone, is_active FROM users WHERE user_id = ?");
        $stmt->execute([$auth['user_id']]);

        Response::success('Profile retrieved', $stmt->fetch());
    }
}