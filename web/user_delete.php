<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;

if ($id) {
    // ==========================================
    // 1. DELETE DARI API RAILWAY
    // ==========================================
    apiRequest('/users/' . $id, 'DELETE', null, $_SESSION['token']);

    // ==========================================
    // 2. SYNC DELETE KE LOCALHOST
    // ==========================================
    if ($conn_local) {
        $stmt = $conn_local->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            error_log("Sync delete gagal: " . $stmt->error);
        }
        $stmt->close();
    }
}

header("Location: users.php");
exit;
?>