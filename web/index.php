<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];

$response = apiRequest('/profile', 'GET', null, $_SESSION['token']);
if ($response['status_code'] === 401) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$userData = $response['body']['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h3>Welcome <?= htmlspecialchars($_SESSION['full_name']) ?> (<?= htmlspecialchars($role) ?>)</h3>

  <div class="mb-3">
    <?php if ($role === 'admin'): ?>
        <a href="users.php" class="btn btn-dark">Manage Users</a>
        <a href="exams.php" class="btn btn-dark">Manage Exams</a>
    <?php elseif ($role === 'lecturer'): ?>
        <a href="results.php" class="btn btn-dark">Update Results</a>
        <a href="exams.php" class="btn btn-dark">View Exams</a>
    <?php else: ?>
        <a href="results.php" class="btn btn-dark">View My Results</a>
        <a href="exams.php" class="btn btn-dark">View My Exams</a>
    <?php endif; ?>
    <a href="logout.php" class="btn btn-danger">Logout</a>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card text-bg-primary">
        <div class="card-body">
          <h5>Your Name</h5>
          <p class="fs-3"><?= htmlspecialchars($userData['full_name'] ?? $_SESSION['full_name']) ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card text-bg-success">
        <div class="card-body">
          <h5>Your Role</h5>
          <p class="fs-3"><?= htmlspecialchars($userData['role'] ?? $role) ?></p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>