<?php
session_start();
require_once __DIR__ . '/config.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $no_ic = trim($_POST["no_ic"]);

    if ($email === "" || $no_ic === "") {
        $error = "Please enter both email and IC number.";
    } else {
        $response = apiRequest('/login', 'POST', ['email' => $email, 'no_ic' => $no_ic]);
        if ($response['status_code'] === 200 && ($response['body']['status'] ?? '') === 'success') {
            $_SESSION['user_id']   = $response['body']['data']['user_id'];
            $_SESSION['full_name'] = $response['body']['data']['full_name'];
            $_SESSION['role']      = $response['body']['data']['role'];
            $_SESSION['token']     = $response['body']['data']['token'];
            header("Location: index.php"); exit;
        } else {
            $error = $response['body']['message'] ?? 'Login failed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="login-shell">
  <div class="login-card">
    <div class="logo-mark">⚡</div>
    <h1>Welcome Back</h1>
    <p class="sub">Access the ExamFlow control center</p>

    <?php if ($error): ?>
      <div class="alert-neo danger">
        <i class="bi bi-exclamation-octagon-fill"></i>
        <div><?= htmlspecialchars($error) ?></div>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="field">
        <label><i class="bi bi-envelope-fill"></i> Email Address</label>
        <input type="text" name="email" class="input-neo" placeholder="admin@uni.edu" required autofocus>
      </div>
      <div class="field">
        <label><i class="bi bi-shield-lock-fill"></i> IC Number</label>
        <input type="text" name="no_ic" class="input-neo" placeholder="900101010101" required>
      </div>
      <button type="submit" class="btn-neo violet block" style="padding:0.9rem;">
        <i class="bi bi-box-arrow-in-right"></i> Enter the System
      </button>
    </form>

    <div class="demo-box">
      <strong>DEMO ACCOUNTS</strong><br>
      <span style="color:var(--cyan)">admin</span>   → admin@uni.edu / 900101010101<br>
      <span style="color:var(--cyan)">lecturer</span> → tan@uni.edu / 800202020202<br>
      <span style="color:var(--cyan)">student</span>  → siti@student.edu / 010203040506
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>