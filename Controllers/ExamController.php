<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

class ExamController {

    public function index($id = null) {
        $auth = AuthMiddleware::verifyToken();
        $pdo  = Database::connect();

        if ($id) {
            $stmt = $pdo->prepare("
                SELECT e.*, c.course_code, c.course_title, s.subject_name 
                FROM examinations e 
                JOIN courses c ON e.course_id = c.course_id
                JOIN subjects s ON e.subject_id = s.subject_id
                WHERE e.exam_id = ?
            ");
            $stmt->execute([$id]);
            $exam = $stmt->fetch();
            if (!$exam) Response::error('Exam not found', 404);

            if ($auth['role'] === 'student') {
                $check = $pdo->prepare("SELECT 1 FROM results WHERE student_id = ? AND subject_id = ?");
                $check->execute([$auth['user_id'], $exam['subject_id']]);
                if (!$check->fetch()) Response::error('Access denied', 403);
            }

            Response::success('Exam found', $exam);
        }

        $sql    = "SELECT e.*, c.course_code, c.course_title, s.subject_name 
                   FROM examinations e 
                   JOIN courses c ON e.course_id = c.course_id
                   JOIN subjects s ON e.subject_id = s.subject_id";
        $params = [];
        $where  = [];

        // Student: hanya exam untuk subjek yang dia ambil
        if ($auth['role'] === 'student') {
            $where[]  = 'e.subject_id IN (SELECT subject_id FROM results WHERE student_id = ?)';
            $params[] = $auth['user_id'];
        }

        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= " ORDER BY e.exam_date ASC, e.start_time ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        Response::success('Examinations retrieved', $stmt->fetchAll());
    }

    // POST /examinations (ADMIN SAHAJA)
    public function store() {
        AuthMiddleware::requireRole(['admin']);
        $data = json_decode(file_get_contents('php://input'), true);

        foreach (['course_id','subject_id','exam_date','start_time','end_time','venue'] as $f) {
            if (empty($data[$f])) Response::error("Field '$f' required", 400);
        }

        $pdo = Database::connect();

        $check = $pdo->prepare("SELECT 1 FROM subjects WHERE subject_id = ? AND course_id = ?");
        $check->execute([$data['subject_id'], $data['course_id']]);
        if (!$check->fetch()) {
            Response::error('Subject does not belong to selected course', 400);
        }

        $stmt = $pdo->prepare("INSERT INTO examinations (course_id, subject_id, exam_date, start_time, end_time, venue, status) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['course_id'], $data['subject_id'], $data['exam_date'],
            $data['start_time'], $data['end_time'], $data['venue'],
            $data['status'] ?? 'scheduled'
        ]);
        Response::success('Exam created', ['exam_id' => $pdo->lastInsertId()], 201);
    }

    // PUT /examinations/{id} (ADMIN SAHAJA)
    public function update($id) {
        AuthMiddleware::requireRole(['admin']);
        $data = json_decode(file_get_contents('php://input'), true);

        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM examinations WHERE exam_id = ?");
        $stmt->execute([$id]);
        $e = $stmt->fetch();
        if (!$e) Response::error('Exam not found', 404);

        $update = $pdo->prepare("UPDATE examinations SET subject_id=?, exam_date=?, start_time=?, end_time=?, venue=?, status=? WHERE exam_id=?");
        $update->execute([
            $data['subject_id'] ?? $e['subject_id'],
            $data['exam_date']  ?? $e['exam_date'],
            $data['start_time'] ?? $e['start_time'],
            $data['end_time']   ?? $e['end_time'],
            $data['venue']      ?? $e['venue'],
            $data['status']     ?? $e['status'],
            $id
        ]);
        Response::success('Exam updated');
    }

    // DELETE /examinations/{id} (ADMIN SAHAJA)
    public function destroy($id) {
        AuthMiddleware::requireRole(['admin']);
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM examinations WHERE exam_id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) Response::error('Exam not found', 404);

        $pdo->prepare("DELETE FROM examinations WHERE exam_id = ?")->execute([$id]);
        Response::success('Exam deleted');
    }
}