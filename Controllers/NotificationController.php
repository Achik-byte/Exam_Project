<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

class NotificationController {

    // GET /notifications
    public function index($id = null) {
        $auth = AuthMiddleware::verifyToken();
        $pdo  = Database::connect();

        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE notification_id = ?");
            $stmt->execute([$id]);
            $n = $stmt->fetch();
            if (!$n) Response::error('Notification not found', 404);
            Response::success('Notification found', $n);
        }

        if ($auth['role'] === 'student') {
            // Student: senarai notifikasi peribadi dia
            $stmt = $pdo->prepare("
                SELECT notification_id, title, message, is_read, created_at 
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$auth['user_id']]);
            $rows = $stmt->fetchAll();
        } else {
            // Admin/Lecturer: GROUP supaya 1 baris per broadcast
            $stmt = $pdo->query("
                SELECT 
                    MIN(notification_id) AS notification_id,
                    title,
                    message,
                    MIN(created_at) AS created_at,
                    COUNT(*) AS recipient_count
                FROM notifications
                GROUP BY title, message
                ORDER BY MIN(created_at) DESC
            ");
            $rows = $stmt->fetchAll();
        }

        Response::success('Notifications retrieved', $rows);
    }

    // POST /notifications
    public function store() {
        $auth = AuthMiddleware::requireRole(['admin', 'lecturer']);
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['title']) || empty($data['message'])) {
            Response::error('title and message required', 400);
        }

        $pdo = Database::connect();

        // ===== BROADCAST =====
        if (!empty($data['to_all']) && $data['to_all'] == 1) {
            $recipients = [];

            if ($auth['role'] === 'admin') {
                // Admin: SEMUA student + SEMUA lecturer + SEMUA admin
                $stmt = $pdo->query("
                    SELECT user_id FROM users 
                    WHERE role IN ('admin', 'student', 'lecturer') 
                      AND is_active = 1
                ");
                $recipients = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            } else {
                // Lecturer: student dalam kursus dia + diri sendiri
                $stmt = $pdo->prepare("
                    SELECT DISTINCT u.user_id 
                    FROM users u
                    JOIN results r ON r.student_id = u.user_id
                    JOIN courses c ON r.course_id = c.course_id
                    WHERE c.lecturer_id = ? 
                      AND u.role = 'student' 
                      AND u.is_active = 1
                ");
                $stmt->execute([$auth['user_id']]);
                $recipients = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                // Tambah diri sendiri (lecturer)
                $recipients[] = $auth['user_id'];
            }

            $recipients = array_unique($recipients);

            if (empty($recipients)) Response::error('No recipients found', 404);

            $insert = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?,?,?)");
            $count = 0;
            foreach ($recipients as $uid) {
                $insert->execute([$uid, $data['title'], $data['message']]);
                $count++;
            }

            Response::success("Notification sent to $count user(s)", ['sent_count' => $count], 201);
        }

        // ===== SINGLE RECIPIENT =====
        if (empty($data['user_id'])) Response::error('user_id required (or set to_all=1)', 400);

        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?,?,?)");
        $stmt->execute([$data['user_id'], $data['title'], $data['message']]);

        Response::success('Notification created', ['notification_id' => $pdo->lastInsertId()], 201);
    }

    // PUT /notifications/{id}
    public function update($id) {
        $auth = AuthMiddleware::verifyToken();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$id) Response::error('ID required', 400);

        $pdo = Database::connect();

        if ($auth['role'] === 'student') {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
            $stmt->execute([$id, $auth['user_id']]);
            if ($stmt->rowCount() === 0) Response::error('Not found or access denied', 404);
            Response::success('Marked as read');
        }

        $stmt = $pdo->prepare("UPDATE notifications SET title=?, message=?, is_read=? WHERE notification_id=?");
        $stmt->execute([
            $data['title'] ?? '',
            $data['message'] ?? '',
            $data['is_read'] ?? 0,
            $id
        ]);
        Response::success('Notification updated');
    }

    // DELETE /notifications/{id}
    public function destroy($id) {
        AuthMiddleware::requireRole(['admin']);
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE notification_id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) Response::error('Not found', 404);
        Response::success('Notification deleted');
    }
}