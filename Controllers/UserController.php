<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

class UserController {

    // GET /users (admin sahaja)
    public function index($id = null) {
        AuthMiddleware::requireRole(['admin']);
        $pdo = Database::connect();

        if ($id) {
            $stmt = $pdo->prepare("SELECT user_id, full_name, email, role, matric_no, phone, is_active, created_at FROM users WHERE user_id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            if (!$user) Response::error('User not found', 404);
            Response::success('User found', $user);
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $limit   = min(100, max(1, (int)($_GET['limit'] ?? 100)));
        $offset  = ($page - 1) * $limit;

        $where  = [];
        $params = [];

        if (!empty($_GET['role'])) {
            $where[]  = 'role = ?';
            $params[] = $_GET['role'];
        }
        if (!empty($_GET['search'])) {
            $where[]  = '(full_name LIKE ? OR email LIKE ?)';
            $params[] = '%' . $_GET['search'] . '%';
            $params[] = '%' . $_GET['search'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sort  = in_array($_GET['sort'] ?? '', ['user_id','full_name','email','role','created_at']) ? $_GET['sort'] : 'user_id';
        $order = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT user_id, full_name, email, role, matric_no, phone, is_active, created_at 
                FROM users $whereSql ORDER BY $sort $order LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM users $whereSql");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        Response::success('Users retrieved', [
            'pagination' => [
                'page'  => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
            'users' => $users,
        ]);
    }

    // GET /students
    // - Admin: semua student
    // - Lecturer: student dalam kursus yang dia ajar
    public function students($id = null) {
        $auth = AuthMiddleware::requireRole(['admin', 'lecturer']);
        $pdo  = Database::connect();

        if ($auth['role'] === 'admin') {
            $stmt = $pdo->query("
                SELECT user_id, full_name, email, matric_no 
                FROM users 
                WHERE role = 'student' AND is_active = 1 
                ORDER BY full_name
            ");
        } else {
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.user_id, u.full_name, u.email, u.matric_no 
                FROM users u
                JOIN results r ON r.student_id = u.user_id
                JOIN courses c ON r.course_id = c.course_id
                WHERE u.role = 'student' 
                  AND c.lecturer_id = ? 
                  AND u.is_active = 1
                ORDER BY u.full_name
            ");
            $stmt->execute([$auth['user_id']]);
        }

        Response::success('Students retrieved', $stmt->fetchAll());
    }

    // POST /users
    public function store() {
        AuthMiddleware::requireRole(['admin']);
        $data = json_decode(file_get_contents('php://input'), true);

        foreach (['full_name','email','no_ic','role'] as $f) {
            if (empty($data[$f])) Response::error("Field '$f' required", 400);
        }

        if (!in_array($data['role'], ['admin','lecturer','student'])) {
            Response::error('Invalid role', 400);
        }

        $pdo = Database::connect();

        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$data['email']]);
        if ($check->fetch()) Response::error('Email already exists', 409);

        $hashed = password_hash($data['no_ic'], PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name,email,password,role,matric_no,no_ic,phone,is_active) VALUES (?,?,?,?,?,?,?,1)");
            $stmt->execute([
                $data['full_name'], $data['email'], $hashed, $data['role'],
                $data['matric_no'] ?? null, $data['no_ic'], $data['phone'] ?? null
            ]);
            Response::success('User created', ['user_id' => $pdo->lastInsertId()], 201);
        } catch (\PDOException $e) {
            Response::error('Failed to create user: ' . $e->getMessage(), 500);
        }
    }

    // PUT /users/{id}
    public function update($id) {
        AuthMiddleware::requireRole(['admin']);
        $data = json_decode(file_get_contents('php://input'), true);

        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) Response::error('User not found', 404);

        $hashed = $user['password'];
        if (!empty($data['no_ic']) && $data['no_ic'] !== $user['no_ic']) {
            $hashed = password_hash($data['no_ic'], PASSWORD_DEFAULT);
        }

        $update = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, matric_no=?, no_ic=?, phone=?, is_active=?, password=? WHERE user_id=?");
        $update->execute([
            $data['full_name'] ?? $user['full_name'],
            $data['email']     ?? $user['email'],
            $data['role']      ?? $user['role'],
            $data['matric_no'] ?? $user['matric_no'],
            $data['no_ic']     ?? $user['no_ic'],
            $data['phone']     ?? $user['phone'],
            $data['is_active'] ?? $user['is_active'],
            $hashed,
            $id
        ]);

        Response::success('User updated successfully');
    }

    // DELETE /users/{id}
    public function destroy($id) {
        AuthMiddleware::requireRole(['admin']);
        if (!$id) Response::error('User ID required', 400);

        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) Response::error('User not found', 404);
        Response::success('User deleted successfully');
    }
    // GET /verify/{id} - PUBLIC (no auth) - untuk QR code verification
public function verify($id) {
    if (!$id) Response::error('Student ID required', 400);

    $pdo = Database::connect();
    $stmt = $pdo->prepare("
        SELECT user_id, full_name, matric_no, email, phone
        FROM users 
        WHERE user_id = ? AND role = 'student' AND is_active = 1
    ");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) Response::error('Student not found', 404);

    // Ambil subjects student
    $subjStmt = $pdo->prepare("
    SELECT s.subject_name, c.course_code, c.course_title
    FROM results r
    JOIN subjects s ON r.subject_id = s.subject_id
    JOIN courses c ON r.course_id = c.course_id
    WHERE r.student_id = ?
    ORDER BY c.course_code, s.subject_name
    ");
    $subjStmt->execute([$id]);
    $subjects = $subjStmt->fetchAll();

    Response::success('Student verified', [
        'student'  => $student,
        'subjects' => $subjects,
    ]);
}
}