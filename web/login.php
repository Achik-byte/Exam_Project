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
</head>
<body>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <h3 class="mb-3">Login</h3>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" action="login.php">
        <div class="mb-3"><label>Email</label>
          <input type="text" name="email" class="form-control" required></div>
        <div class="mb-3"><label>IC Number</label>
          <input type="text" name="no_ic" class="form-control" required></div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>