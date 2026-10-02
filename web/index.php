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
$greeting = 'Hi';
// Ambil data untuk stats
$statsData = ['users' => 0, 'exams' => 0, 'notifications' => 0];
if ($role === 'admin') {
    $u = apiRequest('/users', 'GET', null, $_SESSION['token']);
    $statsData['users'] = count($u['body']['data']['users'] ?? $u['body']['data'] ?? []);
}
$e = apiRequest('/examinations', 'GET', null, $_SESSION['token']);
$statsData['exams'] = count($e['body']['data'] ?? []);
$n = apiRequest('/notifications', 'GET', null, $_SESSION['token']);
$statsData['notifications'] = count($n['body']['data'] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>
    .bento-dash {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 1.15rem;
      margin-bottom: 1.5rem;
    }
    .bento-dash > * { grid-column: span 6; }
    .bento-dash .b-2 { grid-column: span 2; }
    .bento-dash .b-3 { grid-column: span 3; }
    .bento-dash .b-4 { grid-column: span 4; }
    @media (max-width: 900px) {
      .bento-dash .b-2, .bento-dash .b-3, .bento-dash .b-4 { grid-column: span 6; }
    }
    .hero-glass {
      background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(168,85,247,0.2), rgba(236,72,153,0.15));
      border: 1px solid var(--glass-line-2);
      border-radius: var(--r-lg);
      padding: 2rem;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(20px);
    }
    .hero-glass::before {
      content: '';
      position: absolute; top: -50%; right: -10%;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(168,85,247,0.3), transparent 65%);
      border-radius: 50%;
    }
    .hero-glass h1 { font-size: 2.4rem; margin-bottom: 0.35rem; position: relative; }
    .hero-glass .sub { color: var(--ink-2); margin: 0; position: relative; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass">

  <!-- HERO -->
  <div class="hero-glass" style="margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
      <div>
        <span class="eyebrow" style="display:inline-flex;align-items:center;gap:0.5rem;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.15em;color:#a5b4fc;font-weight:600;padding:0.4rem 0.85rem;background:rgba(255,255,255,0.06);border:1px solid var(--glass-line);border-radius:999px;margin-bottom:0.85rem;">◆ Dashboard</span>
        <h1 style="color:white;"><?= $greeting ?>, <em style="font-style:italic;background:linear-gradient(135deg,#a5b4fc,#f0abfc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?= htmlspecialchars($firstName) ?></em></h1>
        <p class="sub">Welcome back. Here's your activity snapshot.</p>
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.5rem;">
        <span class="pill-glass blue" style="padding:0.6rem 1rem;font-size:0.8rem;text-transform:uppercase;">
          <i class="bi bi-shield-fill-check"></i> <?= htmlspecialchars($role) ?>
        </span>
        <span class="pill-glass purple" style="padding:0.5rem 0.9rem;font-size:0.75rem;">
          <i class="bi bi-calendar3"></i> <?= date('d M Y') ?>
        </span>
      </div>
    </div>
  </div>

  <!-- BENTO GRID -->
  <div class="bento-dash">
    <!-- Account Info -->
    <div class="b-2">
      <div class="stat-glass blue">
        <div class="icon"><i class="bi bi-person-badge-fill"></i></div>
        <div class="lbl">Account</div>
        <div class="val val-sm"><?= htmlspecialchars($userData['full_name'] ?? $_SESSION['full_name']) ?></div>
      </div>
    </div>
    <!-- Email -->
    <div class="b-2">
      <div class="stat-glass purple">
        <div class="icon"><i class="bi bi-envelope-fill"></i></div>
        <div class="lbl">Email</div>
        <div class="val val-sm"><?= htmlspecialchars($userData['email'] ?? '—') ?></div>
      </div>
    </div>
    <!-- Role -->
    <div class="b-2">
      <div class="stat-glass green">
        <div class="icon"><i class="bi bi-shield-fill-check"></i></div>
        <div class="lbl">Access</div>
        <div class="val" style="text-transform:capitalize;"><?= htmlspecialchars($role) ?></div>
      </div>
    </div>

    <!-- Big stat: Exams -->
    <div class="b-3">
      <div class="stat-glass amber" style="height:100%;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div class="icon"><i class="bi bi-calendar-event-fill"></i></div>
          <i class="bi bi-arrow-up-right" style="color:var(--ink-3);"></i>
        </div>
        <div class="lbl">Total Exams</div>
        <div class="val" style="font-size:2.5rem;"><?= $statsData['exams'] ?></div>
        <p style="color:var(--ink-3);font-size:0.82rem;margin-top:0.5rem;">Scheduled examinations in the system</p>
      </div>
    </div>

    <!-- Big stat: Users/Notifications -->
    <div class="b-3">
      <?php if ($role === 'admin'): ?>
      <div class="stat-glass blue" style="height:100%;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div class="icon"><i class="bi bi-people-fill"></i></div>
          <i class="bi bi-arrow-up-right" style="color:var(--ink-3);"></i>
        </div>
        <div class="lbl">Total Users</div>
        <div class="val" style="font-size:2.5rem;"><?= $statsData['users'] ?></div>
        <p style="color:var(--ink-3);font-size:0.82rem;margin-top:0.5rem;">Registered accounts on the platform</p>
      </div>
      <?php else: ?>
      <div class="stat-glass purple" style="height:100%;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div class="icon"><i class="bi bi-bell-fill"></i></div>
          <i class="bi bi-arrow-up-right" style="color:var(--ink-3);"></i>
        </div>
        <div class="lbl">Messages</div>
        <div class="val" style="font-size:2.5rem;"><?= $statsData['notifications'] ?></div>
        <p style="color:var(--ink-3);font-size:0.82rem;margin-top:0.5rem;">Notifications in your inbox</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="card-glass">
    <div style="margin-bottom:1.5rem;">
      <h3 style="margin:0;color:white;">Quick actions</h3>
      <p style="color:var(--ink-3);font-size:0.9rem;margin:0.35rem 0 0 0;">Jump straight to what matters most.</p>
    </div>

    <div class="grid-glass">
      <?php if ($role === 'admin'): ?>
        <a href="users.php" class="tile-glass" style="text-decoration:none;">
          <span class="code">USERS</span>
          <div class="name">Manage Users</div>
          <div class="meta"><span class="pill-glass gray"><i class="bi bi-people-fill"></i> Add · Edit · Remove</span></div>
        </a>
        <a href="exams.php" class="tile-glass green" style="text-decoration:none;">
          <span class="code">EXAMS</span>
          <div class="name">Manage Exams</div>
          <div class="meta"><span class="pill-glass gray"><i class="bi bi-calendar-plus-fill"></i> Schedule & Update</span></div>
        </a>
        <a href="notifications.php" class="tile-glass amber" style="text-decoration:none;">
          <span class="code">INBOX</span>
          <div class="name">Send Notification</div>
          <div class="meta"><span class="pill-glass gray"><i class="bi bi-megaphone-fill"></i> Broadcast</span></div>
        </a>
      <?php elseif ($role === 'lecturer'): ?>
        <a href="results.php" class="tile-glass" style="text-decoration:none;">
          <span class="code">GRADING</span>
          <div class="name">Update Results</div>
          <div class="meta"><span class="pill-glass gray"><i class="bi bi-pencil-square"></i> Grade Students</span></div>
        </a>
        <a href="exams.php" class="tile-glass green" style="text-decoration:none;">
          <span class="code">EXAMS</span>
          <div class="name">View Exams</div>
          <div class="meta"><span class="pill-glass gray"><i class="bi bi-calendar-week-fill"></i> Timetable</span></div>
        </a>
        <a href="notifications.php" class="tile-glass amber" style="text-decoration:none;">
          <span class="code">INBOX</span>
          <div class="name">Notify Students</div>
          <div class="meta"><span class="pill-glass gray"><i class="bi bi-send-fill"></i> Send Updates</span></div>
        </a>
      <?php else: ?>
        <a href="my_subjects.php" class="tile-glass" style="text-decoration:none;">
          <span class="code">SUBJECTS</span>
          <div class="name">My Subjects</div>
          <div class="meta"><span class="pill-glass gray"><i class="bi bi-journal-bookmark-fill"></i> Enrollments</span></div>
        </a>
        <a href="results.php" class="tile-glass green" style="text-decoration:none;">
          <span class="code">RESULTS</span>
          <div class="name">View Results</div>
          <div class="meta"><span class="pill-glass gray"><i class="bi bi-bar-chart-fill"></i> Marks & Grades</span></div>
        </a>
        <a href="exams.php" class="tile-glass amber" style="text-decoration:none;">
          <span class="code">EXAMS</span>
          <div class="name">Exam Timetable</div>
          <div class="meta"><span class="pill-glass gray"><i class="bi bi-calendar-week-fill"></i> Upcoming</span></div>
        </a>
      <?php endif; ?>
      <a href="logout.php" class="tile-glass pink" style="text-decoration:none;">
        <span class="code">SESSION</span>
        <div class="name">Sign Out</div>
        <div class="meta"><span class="pill-glass gray"><i class="bi bi-box-arrow-right"></i> End Session</span></div>
      </a>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>