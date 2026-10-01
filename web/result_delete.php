<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if ($id) {
    apiRequest('/results/' . $id, 'DELETE', null, $_SESSION['token']);
}
header("Location: results.php");
exit;