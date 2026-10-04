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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern">

  <div class="page-header">
    <div>
      <h1>Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) ?>! 👋</h1>
      <p class="subtitle">Here's an overview of your examination system</p>
    </div>
    <span class="badge <?= $role === 'admin' ? 'bg-primary' : ($role === 'lecturer' ? 'bg-info' : 'bg-success') ?>" style="padding:0.65rem 1.15rem;font-size:0.8rem;">
      <i class="bi bi-shield-fill-check"></i> <?= strtoupper($role) ?>
    </span>
  </div>

  <div class="stat-grid">
    <div class="stat-card primary">
      <div class="stat-icon"><i class="bi bi-person-badge-fill"></i></div>
      <div class="stat-label">Your Name</div>
      <div class="stat-value" style="font-size:1.5rem;"><?= htmlspecialchars($userData['full_name'] ?? $_SESSION['full_name']) ?></div>
    </div>
    <div class="stat-card success">
      <div class="stat-icon"><i class="bi bi-shield-check"></i></div>
      <div class="stat-label">Access Level</div>
      <div class="stat-value" style="font-size:1.5rem;"><?= ucfirst(htmlspecialchars($role)) ?></div>
    </div>
    <div class="stat-card info">
      <div class="stat-icon"><i class="bi bi-envelope-fill"></i></div>
      <div class="stat-label">Email</div>
      <div class="stat-value" style="font-size:1.1rem;"><?= htmlspecialchars($userData['email'] ?? '-') ?></div>
    </div>
  </div>

  <div class="card-modern">
    <h4 style="margin-top:0;color:#ffffff;font-weight:800;">Quick Actions</h4>
    <p class="text-muted" style="margin-bottom:1.25rem;">Navigate to the most common tasks</p>
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
      <?php if ($role === 'admin'): ?>
        <a href="users.php" class="btn btn-primary"><i class="bi bi-people-fill"></i> Manage Users</a>
        <a href="exams.php" class="btn btn-success"><i class="bi bi-calendar-event-fill"></i> Manage Exams</a>
        <a href="notifications.php" class="btn btn-warning"><i class="bi bi-bell-fill"></i> Send Notification</a>
      <?php elseif ($role === 'lecturer'): ?>
        <a href="results.php" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Update Results</a>
        <a href="exams.php" class="btn btn-info"><i class="bi bi-calendar-event-fill"></i> View Exams</a>
        <a href="notifications.php" class="btn btn-warning"><i class="bi bi-bell-fill"></i> Send Notification</a>
      <?php else: ?>
        <a href="my_subjects.php" class="btn btn-primary"><i class="bi bi-journal-text"></i> My Subjects</a>
        <a href="results.php" class="btn btn-success"><i class="bi bi-bar-chart-fill"></i> View Results</a>
        <a href="exams.php" class="btn btn-info"><i class="bi bi-calendar-event-fill"></i> View Exams</a>
      <?php endif; ?>
      <a href="logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>