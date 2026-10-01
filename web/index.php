<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { session_destroy(); header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];
$response = apiRequest('/profile', 'GET', null, $_SESSION['token']);
if ($response['status_code'] === 401) { session_destroy(); header("Location: login.php"); exit; }
$userData = $response['body']['data'] ?? [];
$firstName = explode(' ', $_SESSION['full_name'])[0];
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum">

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ Dashboard</span>
      <h1><?= $greeting ?>, <em><?= htmlspecialchars($firstName) ?></em></h1>
      <p class="sub">Here's what's happening in your ExamSys today.</p>
    </div>
    <span class="role-lum <?= htmlspecialchars($role) ?>">
      <i class="bi bi-shield-fill-check"></i> <?= htmlspecialchars($role) ?>
    </span>
  </div>

  <!-- BENTO STATS -->
  <div class="bento mb-3">
    <div class="b-4">
      <div class="stat-lum indigo">
        <div class="icon"><i class="bi bi-person-badge-fill"></i></div>
        <div class="lbl">Account</div>
        <div class="val val-sm"><?= htmlspecialchars($userData['full_name'] ?? $_SESSION['full_name']) ?></div>
      </div>
    </div>
    <div class="b-4">
      <div class="stat-lum mint">
        <div class="icon"><i class="bi bi-shield-fill-check"></i></div>
        <div class="lbl">Access Level</div>
        <div class="val" style="text-transform:capitalize;"><?= htmlspecialchars($role) ?></div>
      </div>
    </div>
    <div class="b-4">
      <div class="stat-lum amber">
        <div class="icon"><i class="bi bi-envelope-fill"></i></div>
        <div class="lbl">Email</div>
        <div class="val val-sm"><?= htmlspecialchars($userData['email'] ?? '—') ?></div>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="card-lum flat">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:0.5rem;margin-bottom:1.5rem;">
      <div>
        <h3 style="font-size:1.5rem;margin:0;">Quick actions</h3>
        <p class="text-muted-lum" style="margin:0.35rem 0 0 0;font-size:0.85rem;">Jump straight to what matters most.</p>
      </div>
    </div>

    <div class="grid-lum">
      <?php if ($role === 'admin'): ?>
        <a href="users.php" class="tile-lum indigo" style="text-decoration:none;">
          <span class="code">USERS</span>
          <div class="name">Manage Users</div>
          <div class="meta"><span class="pill gray"><i class="bi bi-people-fill"></i> Add · Edit · Remove</span></div>
        </a>
        <a href="exams.php" class="tile-lum mint" style="text-decoration:none;">
          <span class="code">EXAMS</span>
          <div class="name">Manage Exams</div>
          <div class="meta"><span class="pill gray"><i class="bi bi-calendar-plus-fill"></i> Schedule & Update</span></div>
        </a>
        <a href="notifications.php" class="tile-lum amber" style="text-decoration:none;">
          <span class="code">INBOX</span>
          <div class="name">Send Notification</div>
          <div class="meta"><span class="pill gray"><i class="bi bi-megaphone-fill"></i> Broadcast</span></div>
        </a>
      <?php elseif ($role === 'lecturer'): ?>
        <a href="results.php" class="tile-lum indigo" style="text-decoration:none;">
          <span class="code">GRADING</span>
          <div class="name">Update Results</div>
          <div class="meta"><span class="pill gray"><i class="bi bi-pencil-square"></i> Grade Students</span></div>
        </a>
        <a href="exams.php" class="tile-lum mint" style="text-decoration:none;">
          <span class="code">EXAMS</span>
          <div class="name">View Exams</div>
          <div class="meta"><span class="pill gray"><i class="bi bi-calendar-week-fill"></i> Timetable</span></div>
        </a>
        <a href="notifications.php" class="tile-lum amber" style="text-decoration:none;">
          <span class="code">INBOX</span>
          <div class="name">Notify Students</div>
          <div class="meta"><span class="pill gray"><i class="bi bi-send-fill"></i> Send Updates</span></div>
        </a>
      <?php else: ?>
        <a href="my_subjects.php" class="tile-lum indigo" style="text-decoration:none;">
          <span class="code">SUBJECTS</span>
          <div class="name">My Subjects</div>
          <div class="meta"><span class="pill gray"><i class="bi bi-journal-bookmark-fill"></i> Enrollments</span></div>
        </a>
        <a href="results.php" class="tile-lum mint" style="text-decoration:none;">
          <span class="code">RESULTS</span>
          <div class="name">View Results</div>
          <div class="meta"><span class="pill gray"><i class="bi bi-bar-chart-fill"></i> Marks & Grades</span></div>
        </a>
        <a href="exams.php" class="tile-lum amber" style="text-decoration:none;">
          <span class="code">EXAMS</span>
          <div class="name">Exam Timetable</div>
          <div class="meta"><span class="pill gray"><i class="bi bi-calendar-week-fill"></i> Upcoming</span></div>
        </a>
      <?php endif; ?>
      <a href="logout.php" class="tile-lum coral" style="text-decoration:none;">
        <span class="code">SESSION</span>
        <div class="name">Sign Out</div>
        <div class="meta"><span class="pill gray"><i class="bi bi-box-arrow-right"></i> End Session</span></div>
      </a>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>