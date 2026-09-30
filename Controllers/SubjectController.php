<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

class SubjectController {

    public function index($id = null) {
        AuthMiddleware::verifyToken();
        $pdo = Database::connect();

        if ($id) {
            $stmt = $pdo->prepare("SELECT s.*, c.course_code FROM subjects s JOIN courses c ON s.course_id = c.course_id WHERE s.subject_id = ?");
            $stmt->execute([$id]);
            $subject = $stmt->fetch();
            if (!$subject) Response::error('Subject not found', 404);
            Response::success('Subject found', $subject);
        }

        $courseId = $_GET['course_id'] ?? null;

        if ($courseId) {
            $stmt = $pdo->prepare("SELECT * FROM subjects WHERE course_id = ? ORDER BY subject_name");
            $stmt->execute([$courseId]);
        } else {
            $stmt = $pdo->query("SELECT s.*, c.course_code FROM subjects s JOIN courses c ON s.course_id = c.course_id ORDER BY c.course_code, s.subject_name");
        }

        Response::success('Subjects retrieved', $stmt->fetchAll());
    }
}