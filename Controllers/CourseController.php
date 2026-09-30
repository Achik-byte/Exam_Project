<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

class CourseController {

    public function index($id = null) {
        $auth = AuthMiddleware::verifyToken();
        $pdo  = Database::connect();

        if ($id) {
            $stmt = $pdo->prepare("SELECT c.*, u.full_name AS lecturer_name FROM courses c LEFT JOIN users u ON c.lecturer_id = u.user_id WHERE c.course_id = ?");
            $stmt->execute([$id]);
            $course = $stmt->fetch();
            if (!$course) Response::error('Course not found', 404);
            Response::success('Course found', $course);
        }

        $sql    = "SELECT c.*, u.full_name AS lecturer_name 
                   FROM courses c 
                   LEFT JOIN users u ON c.lecturer_id = u.user_id";
        $params = [];
        $where  = [];

        // FILTER MENGIKUT PERANAN
        if ($auth['role'] === 'lecturer') {
            $where[]  = 'c.lecturer_id = ?';
            $params[] = $auth['user_id'];
        } elseif ($auth['role'] === 'student') {
            $where[]  = 'c.course_id IN (SELECT DISTINCT course_id FROM results WHERE student_id = ?)';
            $params[] = $auth['user_id'];
        }
        // Admin: nampak semua

        if (!empty($_GET['search'])) {
            $where[]  = '(c.course_code LIKE ? OR c.course_title LIKE ?)';
            $params[] = '%' . $_GET['search'] . '%';
            $params[] = '%' . $_GET['search'] . '%';
        }

        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);

        $sort  = in_array($_GET['sort'] ?? '', ['course_id','course_code','course_title']) ? $_GET['sort'] : 'course_code';
        $order = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $sql  .= " ORDER BY c.$sort $order";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        Response::success('Courses retrieved', $stmt->fetchAll());
    }

    public function store() {
        AuthMiddleware::requireRole(['admin']);
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['course_code']) || empty($data['course_title'])) {
            Response::error('course_code and course_title required', 400);
        }

        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_title, credits, lecturer_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['course_code'],
            $data['course_title'],
            $data['credits'] ?? 3,
            $data['lecturer_id'] ?? null
        ]);
        Response::success('Course created', ['course_id' => $pdo->lastInsertId()], 201);
    }

    public function update($id) {
        AuthMiddleware::requireRole(['admin']);
        $data = json_decode(file_get_contents('php://input'), true);

        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
        $stmt->execute([$id]);
        $c = $stmt->fetch();
        if (!$c) Response::error('Course not found', 404);

        $update = $pdo->prepare("UPDATE courses SET course_code=?, course_title=?, credits=?, lecturer_id=? WHERE course_id=?");
        $update->execute([
            $data['course_code']  ?? $c['course_code'],
            $data['course_title'] ?? $c['course_title'],
            $data['credits']      ?? $c['credits'],
            $data['lecturer_id']  ?? $c['lecturer_id'],
            $id
        ]);
        Response::success('Course updated');
    }

    public function destroy($id) {
        AuthMiddleware::requireRole(['admin']);
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) Response::error('Course not found', 404);
        Response::success('Course deleted');
    }
}