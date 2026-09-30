<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if ($id) {
    apiRequest('/users/' . $id, 'DELETE', null, $_SESSION['token']);
}
header("Location: users.php");
exit;