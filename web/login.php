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
  <title>Sign In · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="login-nb">

  <div class="visual">
    <div class="visual-inner">
      <span class="brand-tag">◈ ACADEMIC SUITE 2026</span>
      <h1>Study.<br>Test.<br><em>Excel.</em></h1>
      <p class="desc">A bold platform for scheduling exams, tracking results, and verifying students — designed for institutions that mean business.</p>
    </div>
    <div class="feats">
      <div class="feat">
        <div class="ic"><i class="bi bi-shield-lock-fill"></i></div>
        <div><div class="ttl">Token Security</div><div class="sub">JWT-based authentication</div></div>
      </div>
      <div class="feat">
        <div class="ic"><i class="bi bi-people-fill"></i></div>
        <div><div class="ttl">Role-Aware</div><div class="sub">Admin · Lecturer · Student</div></div>
      </div>
      <div class="feat">
        <div class="ic"><i class="bi bi-qr-code"></i></div>
        <div><div class="ttl">QR Verification</div><div class="sub">Instant exam-hall checks</div></div>
      </div>
    </div>
  </div>

  <div class="form-panel">
    <div class="form-inner">
      <div class="logo">E</div>
      <h2>Sign in</h2>
      <p class="sub2">&gt; Enter your credentials to continue</p>

      <?php if ($error): ?>
        <div class="alert-nb danger"><i class="bi bi-exclamation-triangle-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="field-nb">
          <label>▸ Email Address</label>
          <input type="text" name="email" class="input-nb" placeholder="admin@uni.edu" required autofocus>
        </div>
        <div class="field-nb">
          <label>▸ IC Number</label>
          <input type="text" name="no_ic" class="input-nb" placeholder="900101010101" required>
        </div>
        <button type="submit" class="btn-nb primary block" style="padding:1rem;">
          <i class="bi bi-arrow-right"></i> Continue
        </button>
      </form>

    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>