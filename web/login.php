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
            header("Location: index.php");
            exit;
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
  <title>Login - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="login-page">
  <div class="login-left">
    <div class="login-left-content">
      <h1>ExamSys</h1>
      <p>Secure examination scheduling & result management system for universities and colleges.</p>
      <div class="login-features">
        <div class="login-feature">
          <div class="icon"><i class="bi bi-shield-check"></i></div>
          <div><strong>JWT Authentication</strong><br><span style="opacity:0.8;font-size:0.85rem;">Secure token-based login</span></div>
        </div>
        <div class="login-feature">
          <div class="icon"><i class="bi bi-people"></i></div>
          <div><strong>Role-Based Access</strong><br><span style="opacity:0.8;font-size:0.85rem;">Admin, Lecturer, Student</span></div>
        </div>
        <div class="login-feature">
          <div class="icon"><i class="bi bi-qr-code"></i></div>
          <div><strong>QR Code Verification</strong><br><span style="opacity:0.8;font-size:0.85rem;">Third-party API integration</span></div>
        </div>
      </div>
    </div>
  </div>
  <div class="login-right">
    <div class="login-form-container">
      <h2>Welcome Back</h2>
      <p class="subtitle">Sign in to your account to continue</p>
      <?php if ($error): ?>
        <div class="alert-modern danger">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <div><?= htmlspecialchars($error) ?></div>
        </div>
      <?php endif; ?>
      <form method="POST" action="login.php">
        <div class="form-group">
          <label class="form-label-modern">Email Address</label>
          <input type="text" name="email" class="form-control-modern" placeholder="admin@uni.edu" required>
        </div>
        <div class="form-group">
          <label class="form-label-modern">IC Number</label>
          <input type="text" name="no_ic" class="form-control-modern" placeholder="900101010101" required>
        </div>
        <button type="submit" class="btn-modern primary" style="width:100%;justify-content:center;padding:0.85rem;">
          <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
      </form>
      <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border);font-size:0.85rem;color:var(--gray);">
        <strong style="color:var(--dark-2);">Demo Accounts:</strong><br>
        Admin: admin@uni.edu / 900101010101<br>
        Lecturer: tan@uni.edu / 800202020202<br>
        Student: siti@student.edu / 010203040506
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>