<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

class ResultController {

    // GET /results
    // - Admin: semua
    // - Lecturer: hanya kursus yang dia ajar
    // - Student: hanya result dia sendiri (dengan optional ?graded=1)
    public function index($id = null) {
        $auth = AuthMiddleware::verifyToken();
        $pdo  = Database::connect();

        if ($id) {
            $stmt = $pdo->prepare("
                SELECT r.*, u.full_name, u.matric_no, c.course_code, c.course_title, 
                       s.subject_name, c.lecturer_id
                FROM results r
                JOIN users u ON r.student_id = u.user_id
                JOIN courses c ON r.course_id = c.course_id
                JOIN subjects s ON r.subject_id = s.subject_id
                WHERE r.result_id = ?
            ");
            $stmt->execute([$id]);
            $r = $stmt->fetch();
            if (!$r) Response::error('Result not found', 404);

            if ($auth['role'] === 'student' && $r['student_id'] != $auth['user_id']) {
                Response::error('Access denied', 403);
            }
            if ($auth['role'] === 'lecturer' && $r['lecturer_id'] != $auth['user_id']) {
                Response::error('Access denied', 403);
            }

            Response::success('Result found', $r);
        }

        $sql = "SELECT r.result_id, r.marks, r.grade, r.grade_point, r.published, r.created_at,
                       u.full_name, u.matric_no, 
                       c.course_code, c.course_title, c.lecturer_id,
                       s.subject_name
                FROM results r
                JOIN users u ON r.student_id = u.user_id
                JOIN courses c ON r.course_id = c.course_id
                JOIN subjects s ON r.subject_id = s.subject_id";
        $params = [];
        $where  = [];

        if ($auth['role'] === 'student') {
            $where[]  = 'r.student_id = ?';
            $params[] = $auth['user_id'];

            // Kalau ada ?graded=1, tapis hanya yang ada markah
            if (isset($_GET['graded']) && $_GET['graded'] == '1') {
                $where[] = 'r.marks IS NOT NULL';
            }
        } elseif ($auth['role'] === 'lecturer') {
            $where[]  = 'c.lecturer_id = ?';
            $params[] = $auth['user_id'];
        }

        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= " ORDER BY u.full_name ASC, s.subject_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        Response::success('Results retrieved', $stmt->fetchAll());
    }

    // POST /results (lecturer/admin)
    public function store() {
        $auth = AuthMiddleware::requireRole(['lecturer', 'admin']);
        $data = json_decode(file_get_contents('php://input'), true);

        foreach (['student_id','subject_id','course_id'] as $f) {
            if (!isset($data[$f])) Response::error("Field '$f' required", 400);
        }

        $pdo = Database::connect();

        if ($auth['role'] === 'lecturer') {
            $check = $pdo->prepare("SELECT lecturer_id FROM courses WHERE course_id = ?");
            $check->execute([$data['course_id']]);
            $c = $check->fetch();
            if (!$c || $c['lecturer_id'] != $auth['user_id']) {
                Response::error('You can only create results for your own courses', 403);
            }
        }

        $marks = isset($data['marks']) ? (float)$data['marks'] : null;
        $grade = $data['grade'] ?? null;
        $grade_point = ($marks !== null) ? $this->calcGradePoint($marks) : null;
        $published = $data['published'] ?? 0;

        try {
            $stmt = $pdo->prepare("INSERT INTO results (student_id, subject_id, course_id, marks, grade, grade_point, published) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([
                $data['student_id'], $data['subject_id'], $data['course_id'],
                $marks, $grade, $grade_point, $published
            ]);
            Response::success('Result created', ['result_id' => $pdo->lastInsertId()], 201);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                Response::error('Student already enrolled in this subject', 409);
            }
            Response::error('Failed to create: ' . $e->getMessage(), 500);
        }
    }

    // PUT /results/{id} (lecturer sahaja)
    public function update($id) {
        $auth = AuthMiddleware::requireRole(['lecturer']);
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$id) Response::error('Result ID required', 400);

        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT r.*, c.lecturer_id 
            FROM results r 
            JOIN courses c ON r.course_id = c.course_id 
            WHERE r.result_id = ?
        ");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) Response::error('Result not found', 404);

        if ($r['lecturer_id'] != $auth['user_id']) {
            Response::error('Access denied: You can only edit results for your own courses', 403);
        }

        $marks = $data['marks'] ?? $r['marks'];
        $grade = $data['grade'] ?? $r['grade'];
        $grade_point = $this->calcGradePoint((float)$marks);
        $published = $data['published'] ?? $r['published'];

        $update = $pdo->prepare("UPDATE results SET marks=?, grade=?, grade_point=?, published=? WHERE result_id=?");
        $update->execute([$marks, $grade, $grade_point, $published, $id]);

        Response::success('Result updated successfully');
    }

    // DELETE /results/{id} (admin sahaja)
    public function destroy($id) {
        AuthMiddleware::requireRole(['admin']);
        $pdo = Database::connect();

        $stmt = $pdo->prepare("DELETE FROM results WHERE result_id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) Response::error('Result not found', 404);
        Response::success('Result deleted');
    }

    private function calcGradePoint($marks) {
        if ($marks >= 80) return 4.00;
        if ($marks >= 75) return 3.67;
        if ($marks >= 70) return 3.33;
        if ($marks >= 65) return 3.00;
        if ($marks >= 60) return 2.67;
        if ($marks >= 55) return 2.33;
        if ($marks >= 50) return 2.00;
        return 0.00;
    }
}