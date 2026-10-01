<?php
session_start();
require_once __DIR__ . '/config.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $no_ic = trim($_POST["no_ic"]);
    if ($email === "" || $no_ic === "") $error = "Please enter both email and IC number.";
    else {
        $response = apiRequest('/login', 'POST', ['email' => $email, 'no_ic' => $no_ic]);
        if ($response['status_code'] === 200 && ($response['body']['status'] ?? '') === 'success') {
            $_SESSION['user_id']   = $response['body']['data']['user_id'];
            $_SESSION['full_name'] = $response['body']['data']['full_name'];
            $_SESSION['role']      = $response['body']['data']['role'];
            $_SESSION['token']     = $response['body']['data']['token'];
            header("Location: index.php"); exit;
        } else $error = $response['body']['message'] ?? 'Login failed';
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
<div class="login-lum">

  <div class="visual">
    <div class="visual-inner">
      <span class="tag"><i class="bi bi-stars"></i> Academic Excellence Suite</span>
      <h1>A quieter way<br>to manage <em>examinations.</em></h1>
      <p class="desc">A calm, elegant platform for scheduling, grading, and verifying examinations across your institution.</p>
    </div>

    <div class="feats">
      <div class="feat">
        <div class="ic"><i class="bi bi-shield-check"></i></div>
        <div><div class="ttl">Secure Authentication</div><div class="sub">Token-based access control</div></div>
      </div>
      <div class="feat">
        <div class="ic"><i class="bi bi-people-fill"></i></div>
        <div><div class="ttl">Role-Aware Experience</div><div class="sub">Admin · Lecturer · Student</div></div>
      </div>
      <div class="feat">
        <div class="ic"><i class="bi bi-qr-code"></i></div>
        <div><div class="ttl">QR Verification</div><div class="sub">Instant exam-hall validation</div></div>
      </div>
    </div>
  </div>

  <div class="form-panel">
    <div class="form-inner">
      <div class="logo">◈</div>
      <h2>Welcome back</h2>
      <p class="sub2">Sign in to continue to your dashboard.</p>

      <?php if ($error): ?>
        <div class="alert-lum danger">
          <i class="bi bi-exclamation-octagon-fill"></i>
          <div><?= htmlspecialchars($error) ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="field-lum">
          <label><i class="bi bi-envelope-fill"></i> Email Address</label>
          <input type="text" name="email" class="input-lum" placeholder="admin@uni.edu" required autofocus>
        </div>
        <div class="field-lum">
          <label><i class="bi bi-shield-lock-fill"></i> IC Number</label>
          <input type="text" name="no_ic" class="input-lum" placeholder="900101010101" required>
        </div>
        <button type="submit" class="btn-lum primary block" style="padding:0.9rem;">
          <i class="bi bi-arrow-right"></i> Continue
        </button>
      </form>

      <div class="demo-card">
        <strong><i class="bi bi-key-fill"></i> Demo Accounts</strong>
        <span class="acc">admin</span> · admin@uni.edu / 900101010101<br>
        <span class="acc">lecturer</span> · tan@uni.edu / 800202020202<br>
        <span class="acc">student</span> · siti@student.edu / 010203040506
      </div>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>