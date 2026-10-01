<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    session_destroy();
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];
$response = apiRequest('/profile', 'GET', null, $_SESSION['token']);
if ($response['status_code'] === 401) { session_destroy(); header("Location: login.php"); exit; }
$userData = $response['body']['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-aurora">

  <div class="page-head">
    <div>
      <div class="eyebrow">◆ Command Center</div>
      <h1>Hey, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) ?> 👋</h1>
      <p class="subtitle">Welcome to your ExamFlow dashboard — everything at a glance.</p>
    </div>
    <span class="role-pill <?= htmlspecialchars($role) ?>">
      <i class="bi bi-shield-check"></i> <?= htmlspecialchars($role) ?>
    </span>
  </div>

  <div class="stat-grid">
    <div class="stat-card violet">
      <div class="stat-icon"><i class="bi bi-person-badge-fill"></i></div>
      <div class="stat-label">Account Holder</div>
      <div class="stat-value"><?= htmlspecialchars($userData['full_name'] ?? $_SESSION['full_name']) ?></div>
    </div>
    <div class="stat-card cyan">
      <div class="stat-icon"><i class="bi bi-shield-lock-fill"></i></div>
      <div class="stat-label">Access Level</div>
      <div class="stat-value" style="text-transform:capitalize;"><?= htmlspecialchars($role) ?></div>
    </div>
    <div class="stat-card mint">
      <div class="stat-icon"><i class="bi bi-envelope-at-fill"></i></div>
      <div class="stat-label">Email</div>
      <div class="stat-value" style="font-size:1.05rem;"><?= htmlspecialchars($userData['email'] ?? '—') ?></div>
    </div>
  </div>

  <div class="glass">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:1.25rem;">
      <h3 style="font-size:1.15rem;font-weight:700;margin:0;"><i class="bi bi-rocket-takeoff-fill" style="color:var(--violet-2)"></i> Quick Actions</h3>
      <span class="text-muted-neo">One tap to the essentials</span>
    </div>

    <div class="tile-grid">
      <?php if ($role === 'admin'): ?>
        <a href="users.php" class="tile violet">
          <div class="tile-icon"><i class="bi bi-people-fill"></i></div>
          <div class="tile-body"><div class="tile-title">Manage Users</div><div class="tile-desc">Add · Edit · Remove</div></div>
        </a>
        <a href="exams.php" class="tile cyan">
          <div class="tile-icon"><i class="bi bi-calendar2-plus-fill"></i></div>
          <div class="tile-body"><div class="tile-title">Manage Exams</div><div class="tile-desc">Schedule & update</div></div>
        </a>
        <a href="notifications.php" class="tile amber">
          <div class="tile-icon"><i class="bi bi-megaphone-fill"></i></div>
          <div class="tile-body"><div class="tile-title">Broadcast</div><div class="tile-desc">Send notifications</div></div>
        </a>
      <?php elseif ($role === 'lecturer'): ?>
        <a href="results.php" class="tile violet">
          <div class="tile-icon"><i class="bi bi-pencil-square"></i></div>
          <div class="tile-body"><div class="tile-title">Update Results</div><div class="tile-desc">Grade students</div></div>
        </a>
        <a href="exams.php" class="tile cyan">
          <div class="tile-icon"><i class="bi bi-calendar2-week-fill"></i></div>
          <div class="tile-body"><div class="tile-title">View Exams</div><div class="tile-desc">Exam schedule</div></div>
        </a>
        <a href="notifications.php" class="tile amber">
          <div class="tile-icon"><i class="bi bi-megaphone-fill"></i></div>
          <div class="tile-body"><div class="tile-title">Notify Students</div><div class="tile-desc">Send updates</div></div>
        </a>
      <?php else: ?>
        <a href="my_subjects.php" class="tile violet">
          <div class="tile-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
          <div class="tile-body"><div class="tile-title">My Subjects</div><div class="tile-desc">Enrolled list & QR</div></div>
        </a>
        <a href="results.php" class="tile mint">
          <div class="tile-icon"><i class="bi bi-bar-chart-fill"></i></div>
          <div class="tile-body"><div class="tile-title">My Results</div><div class="tile-desc">Grades & marks</div></div>
        </a>
        <a href="exams.php" class="tile cyan">
          <div class="tile-icon"><i class="bi bi-calendar2-week-fill"></i></div>
          <div class="tile-body"><div class="tile-title">Exam Timetable</div><div class="tile-desc">Upcoming exams</div></div>
        </a>
      <?php endif; ?>
      <a href="logout.php" class="tile pink">
        <div class="tile-icon"><i class="bi bi-box-arrow-right"></i></div>
        <div class="tile-body"><div class="tile-title">Sign Out</div><div class="tile-desc">End session</div></div>
      </a>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>